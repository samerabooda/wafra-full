<?php
// Wafra Gulf — root entry point fallback
// Serves if .htaccess mod_rewrite is disabled
chdir(__DIR__ . '/public');
require __DIR__ . '/public/index.php';
