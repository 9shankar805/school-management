<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$routes = app('router')->getRoutes();
echo "login route exists: " . ($routes->hasNamedRoute('login') ? "yes" : "no") . "\n";
echo "saas.landing route exists: " . ($routes->hasNamedRoute('saas.landing') ? "yes" : "no") . "\n";
