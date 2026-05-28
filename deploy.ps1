# ════════════════════════════════════════════════════════
#  Wafra Gulf — One-Click Deploy Script
#  Usage:  .\deploy.ps1
#  Usage:  .\deploy.ps1 -message "my commit message"
#  Usage:  .\deploy.ps1 -SkipCommit   (called from git hook)
# ════════════════════════════════════════════════════════
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

function UploadFile($localPath, $remoteDir, $fileName) {
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

Write-Host "`n══════════════════════════════════════" -ForegroundColor Cyan
Write-Host " 🚀 Wafra Gulf — Auto Deploy" -ForegroundColor Cyan
Write-Host "══════════════════════════════════════`n" -ForegroundColor Cyan

Set-Location $BASE

# ── Step 1: Git commit & push ─────────────────────────────
if (-not $SkipCommit) {
    $status = git status --porcelain
    if ($status) {
        if (!$message) {
            $ts = Get-Date -Format "yyyy-MM-dd HH:mm"
            $message = "chore: auto-deploy $ts"
        }
        Write-Host "📦 Committing changes..." -ForegroundColor Yellow
        git add -A
        git commit -m $message
        Write-Host "✅ Committed" -ForegroundColor Green
    } else {
        Write-Host "ℹ️  No local changes to commit" -ForegroundColor Gray
    }
}

# ── Step 2: Push to GitHub ────────────────────────────────
Write-Host "`n🔄 Pushing to GitHub..." -ForegroundColor Yellow
git push origin main 2>&1 | Write-Host
Write-Host "✅ Pushed to GitHub" -ForegroundColor Green

# ── Step 3: Get changed files ─────────────────────────────
Write-Host "`n📋 Detecting changed files..." -ForegroundColor Yellow
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

Write-Host "  Found $($deployable.Count) files to deploy`n"

# ── Step 4: Upload files ───────────────────────────────────
Write-Host "📤 Uploading to server..." -ForegroundColor Yellow
$ok = 0; $err = 0; $migrations = @()

foreach ($f in $deployable) {
    $localPath  = Join-Path $BASE $f
    $remoteFull = $REMOTE_BASE + "/" + $f.Replace("\","/")
    $remoteDir  = $remoteFull.Substring(0, $remoteFull.LastIndexOf("/"))
    $fileName   = $remoteFull.Substring($remoteFull.LastIndexOf("/")+1)

    if (UploadFile $localPath $remoteDir $fileName) {
        Write-Host "  ✅ $f" -ForegroundColor Green
        $ok++
    } else {
        Write-Host "  ❌ $f" -ForegroundColor Red
        $err++
    }

    if ($f -like "database/migrations/*") { $migrations += $f }
}

Write-Host "`n  Uploaded: $ok OK  $err errors" -ForegroundColor Cyan

# ── Step 5: Clear view cache (correct timestamp order) ─────
Write-Host "`n🧹 Clearing view cache..." -ForegroundColor Yellow

$res = CpanelAPI @{
    cpanel_jsonapi_user       = $CPANEL_USER
    cpanel_jsonapi_apiversion = "2"
    cpanel_jsonapi_module     = "Fileman"
    cpanel_jsonapi_func       = "listfiles"
    dir                       = $VIEWS_DIR
    showdotfiles              = "1"
}
$cacheFiles = $res.cpanelresult.data | Where-Object { $_.file -match '\.php$' }

# A: overwrite → mtime = NOW
foreach ($cf in $cacheFiles) {
    CpanelAPI @{
        cpanel_jsonapi_user       = $CPANEL_USER
        cpanel_jsonapi_apiversion = "2"
        cpanel_jsonapi_module     = "Fileman"
        cpanel_jsonapi_func       = "savefile"
        dir                       = $VIEWS_DIR
        filename                  = $cf.file
        content                   = "<?php // stale"
    } | Out-Null
}
Write-Host "  Marked $($cacheFiles.Count) cache files as stale"

# B: wait so re-upload gets newer mtime
Start-Sleep -Seconds 2

# C: re-upload ALL blade files → mtime = NOW+2 (critical: must be newer than cache)
$allBlades = Get-ChildItem -Path $BASE -Filter "*.blade.php" -Recurse |
    Where-Object { $_.FullName -notmatch '\\vendor\\' -and $_.FullName -notmatch '\\node_modules\\' }
foreach ($blade in $allBlades) {
    $rel      = $blade.FullName.Replace($BASE+"\","").Replace("\","/")
    $rfull    = "$REMOTE_BASE/$rel"
    UploadFile $blade.FullName $rfull.Substring(0,$rfull.LastIndexOf("/")) $rfull.Substring($rfull.LastIndexOf("/")+1) | Out-Null
}
Write-Host "  Re-uploaded $($allBlades.Count) blade files (source newer than cache — Laravel recompiles)"

# ── Step 6: Migration warning ──────────────────────────────
if ($migrations.Count -gt 0) {
    Write-Host "`n⚠️  NEW MIGRATIONS DETECTED:" -ForegroundColor Yellow
    $migrations | ForEach-Object { Write-Host "   $_" -ForegroundColor Yellow }
    Write-Host "`n   ▶ Run from cPanel Terminal:" -ForegroundColor White
    Write-Host "   cd /home/systemwafragulf/public_html && php artisan migrate --force`n" -ForegroundColor Cyan
}

Write-Host "`n══════════════════════════════════════" -ForegroundColor Green
Write-Host " 🎉 Deployment complete!" -ForegroundColor Green
Write-Host "   https://system-wafragulf.online" -ForegroundColor Green
Write-Host "══════════════════════════════════════`n" -ForegroundColor Green
