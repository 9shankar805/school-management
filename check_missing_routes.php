<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$routes = app('router')->getRoutes();

$content = file_get_contents(__DIR__.'/resources/views/layouts/left-menu.blade.php');
preg_match_all("/route\('([^']+)'\)/", $content, $matches);

$missing = [];
foreach ($matches[1] as $routeName) {
    if (!$routes->hasNamedRoute($routeName)) {
        $missing[] = $routeName;
    }
}

if (empty($missing)) {
    echo "All routes in left-menu.blade.php exist!\n";
} else {
    echo "Missing routes:\n";
    foreach (array_unique($missing) as $route) {
        echo "- " . $route . "\n";
    }
}
