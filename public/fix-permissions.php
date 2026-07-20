<?php
/**
 * Emergency permission + cache fix script.
 * DROP THIS FILE into public/ then hit it once in the browser.
 * DELETE IT IMMEDIATELY after use — do not leave on a live server.
 *
 * Usage: https://your-server/fix-permissions.php?key=fix123
 */

// Simple key guard — change this before deploying
$secret = 'fix123';
if (($_GET['key'] ?? '') !== $secret) {
    http_response_code(403);
    die('Forbidden. Add ?key=fix123 to the URL.');
}

$base    = dirname(__DIR__);
$storage = $base . '/storage';
$cache   = $base . '/bootstrap/cache';

echo '<pre style="font-family:monospace;font-size:13px;padding:20px">';
echo "Base path: {$base}\n\n";

// ── 1. Fix permissions ─────────────────────────────────────────────────────
$dirs = [
    $storage . '/framework/cache',
    $storage . '/framework/cache/data',
    $storage . '/framework/sessions',
    $storage . '/framework/views',
    $storage . '/logs',
    $cache,
];

foreach ($dirs as $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0775, true);
        echo "Created: {$dir}\n";
    }
    if (chmod($dir, 0775)) {
        echo "chmod 775 OK: {$dir}\n";
    } else {
        echo "chmod FAILED: {$dir}\n";
    }
}

// ── 2. Delete all compiled view cache files ────────────────────────────────
$viewsCache = $storage . '/framework/views';
$deleted    = 0;
$failed     = 0;

if (is_dir($viewsCache)) {
    $files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($viewsCache, RecursiveDirectoryIterator::SKIP_DOTS)
    );
    foreach ($files as $file) {
        if ($file->isFile()) {
            if (unlink($file->getRealPath())) {
                $deleted++;
            } else {
                $failed++;
                echo "Could not delete: " . $file->getRealPath() . "\n";
            }
        }
    }
}
echo "\nDeleted {$deleted} compiled view file(s). Failed: {$failed}\n";

// ── 3. Delete framework cache data files ──────────────────────────────────
$cacheData = $storage . '/framework/cache/data';
$cdeleted  = 0;

if (is_dir($cacheData)) {
    $files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($cacheData, RecursiveDirectoryIterator::SKIP_DOTS)
    );
    foreach ($files as $file) {
        if ($file->isFile() && unlink($file->getRealPath())) {
            $cdeleted++;
        }
    }
}
echo "Deleted {$cdeleted} cache data file(s).\n";

// ── 4. Verify the exact broken path ───────────────────────────────────────
$broken = $storage . '/framework/cache/data/85/5f/855f92484c8c414d36c1b25cb24876e30229cbbf';
if (!file_exists($broken)) {
    echo "\n✓ Broken cache file is gone.\n";
} else {
    if (unlink($broken)) {
        echo "\n✓ Broken cache file deleted directly.\n";
    } else {
        echo "\n✗ Could not delete broken file: {$broken}\n";
        echo "  Owner: " . posix_getpwuid(fileowner($broken))['name'] . "\n";
        echo "  Current user: " . get_current_user() . "\n";
    }
}

echo "\n\n✅ Done. DELETE this file now: public/fix-permissions.php\n";
echo '</pre>';
