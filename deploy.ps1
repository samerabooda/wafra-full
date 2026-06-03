# ==============================================
#  Wafra Gulf -- One-Click Deploy Script
#  Usage:  .\deploy.ps1
#  Usage:  .\deploy.ps1 -message "commit msg"
#  Usage:  .\deploy.ps1 -SkipCommit
# ==============================================
param(
    [string]$message    = "",
    [switch]$SkipCommit
)

[Net.ServicePointManager]::ServerCertificateValidationCallback = {$true}
[Net.ServicePointManager]::SecurityProtocol = [Net.SecurityProtocolType]::Tls12
Add-Type -AssemblyName System.Web

$CPANEL_HOST = "173.231.231.39"
$CPANEL_PORT = "2083"
$CPANEL_USER = "systemwafragulf"
$CPANEL_PASS = "D.Vwwvqo9]AblTM%"
$REMOTE_BASE = "/public_html"
$VIEWS_DIR   = "$REMOTE_BASE/storage/framework/views"
$BASE        = $PSScriptRoot

$SKIP = @(".git\",".env","storage\","bootstrap\cache\","node_modules\","vendor\","tmp_writer.php","login-designs-preview.html")

$auth    = [Convert]::ToBase64String([Text.Encoding]::ASCII.GetBytes("${CPANEL_USER}:${CPANEL_PASS}"))
$headers = @{ Authorization = "Basic $auth"; "Content-Type" = "application/x-www-form-urlencoded" }

function CpanelAPI($params) {
    $body = ($params.GetEnumerator() | ForEach-Object {
        [System.Web.HttpUtility]::UrlEncode($_.Key) + "=" + [System.Web.HttpUtility]::UrlEncode($_.Value)
    }) -join "&"
    $r = Invoke-WebRequest -Uri "https://${CPANEL_HOST}:${CPANEL_PORT}/json-api/cpanel" `
        -Method POST -Headers $headers -Body $body -UseBasicParsing -TimeoutSec 60
    return $r.Content | ConvertFrom-Json
}

# Tracks which remote dirs we've already ensured this run (avoid redundant mkdir calls)
$script:EnsuredDirs = @{}

# savefile cannot create missing parent dirs -> it silently "succeeds" but the file
# never lands (this is what made the whole guide/ folder go missing). Walk the path
# under $REMOTE_BASE and mkdir each level (idempotent) before writing.
function EnsureRemoteDir($remoteDir) {
    if ($script:EnsuredDirs.ContainsKey($remoteDir)) { return }
    if ($remoteDir -notlike "$REMOTE_BASE/*" -and $remoteDir -ne $REMOTE_BASE) {
        $script:EnsuredDirs[$remoteDir] = $true; return
    }
    $rel   = $remoteDir.Substring($REMOTE_BASE.Length).TrimStart('/')
    if (-not $rel) { $script:EnsuredDirs[$remoteDir] = $true; return }
    $parts = $rel -split '/'
    $cur   = $REMOTE_BASE
    foreach ($p in $parts) {
        if (-not $p) { continue }
        CpanelAPI @{
            cpanel_jsonapi_user       = $CPANEL_USER
            cpanel_jsonapi_apiversion = "2"
            cpanel_jsonapi_module     = "Fileman"
            cpanel_jsonapi_func       = "mkdir"
            path                      = $cur
            name                      = $p
        } | Out-Null   # errors harmlessly if the dir already exists
        $cur = "$cur/$p"
    }
    $script:EnsuredDirs[$remoteDir] = $true
}

function UploadFile($localPath, $remoteDir, $fileName) {
    EnsureRemoteDir $remoteDir
    $content = Get-Content $localPath -Raw -Encoding UTF8
    $res = CpanelAPI @{
        cpanel_jsonapi_user       = $CPANEL_USER
        cpanel_jsonapi_apiversion = "2"
        cpanel_jsonapi_module     = "Fileman"
        cpanel_jsonapi_func       = "savefile"
        dir                       = $remoteDir
        filename                  = $fileName
        content                   = $content
    }
    return ($res.cpanelresult.event.result -eq 1)
}

Write-Host ""
Write-Host "==========================================" -ForegroundColor Cyan
Write-Host "  Wafra Gulf -- Auto Deploy" -ForegroundColor Cyan
Write-Host "==========================================" -ForegroundColor Cyan
Write-Host ""

Set-Location $BASE

# -- Step 1: Git commit (skipped when called from hook) ----
if (-not $SkipCommit) {
    $status = git status --porcelain
    if ($status) {
        if (!$message) {
            $ts = Get-Date -Format "yyyy-MM-dd HH:mm"
            $message = "chore: auto-deploy $ts"
        }
        Write-Host "Committing changes..." -ForegroundColor Yellow
        git add -A
        git commit -m $message
        Write-Host "Committed." -ForegroundColor Green
    } else {
        Write-Host "No local changes to commit." -ForegroundColor Gray
    }
}

# -- Step 2: Push to GitHub --------------------------------
Write-Host ""
Write-Host "Pushing to GitHub..." -ForegroundColor Yellow
git push origin main 2>&1 | Write-Host
Write-Host "Pushed to GitHub." -ForegroundColor Green

# -- Step 3: Get changed files -----------------------------
Write-Host ""
Write-Host "Detecting changed files..." -ForegroundColor Yellow
$changedFiles = git diff --name-only HEAD~1 HEAD 2>$null
if (-not $changedFiles) {
    $changedFiles = git ls-files
}

$deployable = $changedFiles | Where-Object {
    $f = $_
    $keep = $true
    foreach ($s in $SKIP) { if ($f -like "*$s*") { $keep = $false; break } }
    $keep -and (Test-Path (Join-Path $BASE $f)) -and (-not (Get-Item (Join-Path $BASE $f) -ErrorAction SilentlyContinue).PSIsContainer)
}

Write-Host "  Found $($deployable.Count) files to deploy"
Write-Host ""

# -- Step 4: Upload files ----------------------------------
Write-Host "Uploading to server..." -ForegroundColor Yellow
$ok = 0; $err = 0; $migrations = @()

foreach ($f in $deployable) {
    $localPath  = Join-Path $BASE $f
    $remoteFull = $REMOTE_BASE + "/" + $f.Replace("\","/")
    $remoteDir  = $remoteFull.Substring(0, $remoteFull.LastIndexOf("/"))
    $fileName   = $remoteFull.Substring($remoteFull.LastIndexOf("/")+1)

    if (UploadFile $localPath $remoteDir $fileName) {
        Write-Host "  OK  $f" -ForegroundColor Green
        $ok++
    } else {
        Write-Host "  ERR $f" -ForegroundColor Red
        $err++
    }

    if ($f -like "database/migrations/*") { $migrations += $f }
}

Write-Host ""
Write-Host "  Uploaded: $ok OK  $err errors" -ForegroundColor Cyan

# -- Step 5: Bust view cache for CHANGED blades only (lightweight) --
# Laravel recompiles a view when its source mtime > its compiled-cache mtime.
# Re-uploading ONLY the changed source blades (after a short delay) makes their
# mtime newer than their old compiled cache, forcing a recompile on next request.
# This avoids hammering the host with ~51 API calls per deploy (which triggered
# the "Server is temporarily limiting requests" 429 / LVE resource throttle).
Write-Host ""
Write-Host "Busting view cache (changed blades only)..." -ForegroundColor Yellow

$changedBlades = @($deployable | Where-Object { $_ -like "*.blade.php" })

if ($changedBlades.Count -gt 0) {
    # Wait so the re-upload mtime is clearly newer than the old compiled cache
    Start-Sleep -Seconds 3
    foreach ($f in $changedBlades) {
        $localPath  = Join-Path $BASE $f
        $remoteFull = $REMOTE_BASE + "/" + $f.Replace("\","/")
        $remoteDir  = $remoteFull.Substring(0, $remoteFull.LastIndexOf("/"))
        $fileName   = $remoteFull.Substring($remoteFull.LastIndexOf("/")+1)
        UploadFile $localPath $remoteDir $fileName | Out-Null
        # Throttle between calls to stay under the host rate limiter
        Start-Sleep -Milliseconds 400
    }
    Write-Host "  Re-uploaded $($changedBlades.Count) changed blade file(s) - cache will recompile on next request"
} else {
    Write-Host "  No blade changes - view cache left untouched"
}

# -- Step 6: Migration warning -----------------------------
if ($migrations.Count -gt 0) {
    Write-Host ""
    Write-Host "WARNING: NEW MIGRATIONS DETECTED:" -ForegroundColor Yellow
    $migrations | ForEach-Object { Write-Host "   $_" -ForegroundColor Yellow }
    Write-Host ""
    Write-Host "  Run from cPanel Terminal:" -ForegroundColor White
    Write-Host "  cd /home/systemwafragulf/public_html" -ForegroundColor Cyan
    Write-Host "  php artisan migrate --force" -ForegroundColor Cyan
    Write-Host ""
}

Write-Host ""
Write-Host "==========================================" -ForegroundColor Green
Write-Host "  Deployment complete!" -ForegroundColor Green
Write-Host "  https://system-wafragulf.online" -ForegroundColor Green
Write-Host "==========================================" -ForegroundColor Green
Write-Host ""
