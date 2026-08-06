<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle(Illuminate\Http\Request::capture());

$router = app('router');
$routes = $router->getRoutes();

$menuContent = file_get_contents(__DIR__.'/resources/views/layouts/left-menu.blade.php');

$mentionedRoutes = [];
preg_match_all("/route\('([^']+)'/", $menuContent, $matches);
if (!empty($matches[1])) {
    $mentionedRoutes = array_merge($mentionedRoutes, $matches[1]);
}

$mentionedUrls = [];
preg_match_all("/url\('([^']+)'/", $menuContent, $matches);
if (!empty($matches[1])) {
    $mentionedUrls = array_merge($mentionedUrls, $matches[1]);
}

$missingGetRoutes = [];
$totalGetRoutes = 0;

foreach ($routes as $route) {
    if (in_array('GET', $route->methods()) && !in_array('HEAD', $route->methods()) || (in_array('GET', $route->methods()) && count($route->methods()) == 2)) {
        // Skip API routes, telescope, horizon, etc.
        if (strpos($route->uri(), 'api/') === 0 || strpos($route->uri(), '_ignition') === 0 || strpos($route->uri(), 'sanctum') === 0 || strpos($route->uri(), 'livewire') === 0) {
            continue;
        }
        
        $totalGetRoutes++;
        $name = $route->getName();
        $uri = $route->uri();
        
        $isMentioned = false;
        
        if ($name && in_array($name, $mentionedRoutes)) {
            $isMentioned = true;
        }
        
        if (!$isMentioned) {
            foreach ($mentionedUrls as $mUrl) {
                if (strpos($uri, $mUrl) === 0) {
                    $isMentioned = true;
                    break;
                }
            }
        }
        
        if (!$isMentioned) {
            $missingGetRoutes[] = $name ? "Route: $name (URI: $uri)" : "URI: $uri";
        }
    }
}

echo "Total GET routes (excluding API/system): $totalGetRoutes\n";
echo "Routes mentioned in sidebar: " . count(array_unique($mentionedRoutes)) . " routes, " . count(array_unique($mentionedUrls)) . " URLs\n";
echo "Routes NOT directly connected to sidebar: " . count($missingGetRoutes) . "\n\n";
echo implode("\n", array_slice($missingGetRoutes, 0, 50));
if (count($missingGetRoutes) > 50) {
    echo "\n... and " . (count($missingGetRoutes) - 50) . " more.\n";
}

