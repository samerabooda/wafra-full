<?php
// ══════════════════════════════════════════════════════════
// Wafra Gulf — Emergency Permission Fixer
// Access: https://system-wafragulf.online/fix-permissions.php
// DELETE THIS FILE after running!
// ══════════════════════════════════════════════════════════

// Basic security — only run if secret key matches
$secret = 'wafra-fix-2026';
if (!isset($_GET['key']) || $_GET['key'] !== $secret) {
    die('Unauthorized');
}

$root   = dirname(__DIR__); // public_html root
$report = [];

// Fix directories
$dirs = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($root, RecursiveDirectoryIterator::SKIP_DOTS),
    RecursiveIteratorIterator::SELF_FIRST
);

$fixed_files = 0;
$fixed_dirs  = 0;
$errors      = [];

foreach ($dirs as $path => $info) {
    if (strpos($path, '/.git/') !== false) continue;
    if (strpos($path, '/vendor/') !== false) continue;

    try {
        if ($info->isDir()) {
            // storage and bootstrap/cache need 775
            if (strpos($path, '/storage') !== false || strpos($path, '/bootstrap/cache') !== false) {
                chmod($path, 0775);
            } else {
                chmod($path, 0755);
            }
            $fixed_dirs++;
        } else {
            chmod($path, 0644);
            $fixed_files++;
        }
    } catch (Exception $e) {
        $errors[] = $path . ': ' . $e->getMessage();
    }
}

// Critical files
$critical = [
    $root . '/.htaccess'         => 0644,
    $root . '/index.php'         => 0644,
    $root . '/public/.htaccess'  => 0644,
    $root . '/public/index.php'  => 0644,
    $root . '/storage'           => 0775,
    $root . '/bootstrap/cache'   => 0775,
    $root . '/artisan'           => 0755,
];

foreach ($critical as $path => $perm) {
    if (file_exists($path)) {
        chmod($path, $perm);
        $report[] = sprintf("✅ chmod %04o %s", $perm, str_replace($root, '', $path));
    } else {
        $report[] = "⚠️  Not found: " . str_replace($root, '', $path);
    }
}

// Check .htaccess is readable
$htaccess_ok = is_readable($root . '/.htaccess');
$report[]    = $htaccess_ok ? "✅ .htaccess is readable" : "❌ .htaccess NOT readable";

?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<title>Fix Permissions — Wafra Gulf</title>
<style>
body{font-family:monospace;background:#0C1420;color:#E8EEF5;padding:30px;direction:ltr}
h1{color:#14B87E;margin-bottom:20px}
.ok{color:#14B87E}.err{color:#E04848}.warn{color:#F59820}
pre{background:#111A2B;padding:20px;border-radius:8px;line-height:2}
.del{background:#E04848;color:white;padding:10px 20px;border-radius:6px;text-decoration:none;display:inline-block;margin-top:20px}
</style>
</head>
<body>
<h1>🔧 Wafra Gulf — Permission Fix</h1>
<pre>
Files fixed  : <?= $fixed_files ?>

Directories  : <?= $fixed_dirs ?>

Errors       : <?= count($errors) ?>


<?= implode("\n", $report) ?>


<?php if ($errors): ?>
ERRORS:
<?= implode("\n", array_map(fn($e) => "❌ $e", $errors)) ?>

<?php endif; ?>

<?= $htaccess_ok ? "✅ SUCCESS — Site should work now!" : "❌ FAILED — Contact support" ?>

</pre>
<?php if ($htaccess_ok): ?>
<p style="color:#14B87E;font-size:16px">✅ Permissions fixed! Visit <a href="/" style="color:#2E9FE8">your site</a></p>
<?php endif; ?>
<br>
<a href="?key=<?= $secret ?>&delete=1" class="del">🗑️ Delete this file now (important!)</a>
<?php
if (isset($_GET['delete'])) {
    unlink(__FILE__);
    echo '<p style="color:#14B87E">✅ File deleted. You are secure.</p>';
}
?>
</body>
</html>
