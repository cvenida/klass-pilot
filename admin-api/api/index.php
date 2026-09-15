<?php

// 1. Load Composer Autoloader
require __DIR__ . '/../vendor/autoload.php';

// 2. Prepare writable directory paths inside Vercel's /tmp environment
$storagePath = '/tmp/storage';
$cachePath = '/tmp/bootstrap/cache';

if (!is_dir($storagePath)) {
    mkdir($storagePath . '/framework/views', 0755, true);
    mkdir($storagePath . '/framework/cache', 0755, true);
    mkdir($storagePath . '/framework/sessions', 0755, true);
    mkdir($storagePath . '/logs', 0755, true);
}

if (!is_dir($cachePath)) {
    mkdir($cachePath, 0755, true);
}

$_ENV['APP_SERVICES_CACHE'] = $cachePath . '/services.php';
$_ENV['APP_PACKAGES_CACHE'] = $cachePath . '/packages.php';
$_ENV['APP_CONFIG_CACHE']   = $cachePath . '/config.php';
$_ENV['APP_ROUTES_CACHE']   = $cachePath . '/routes.php';

// 3. Bootstrap Laravel
$app = require __DIR__ . '/../bootstrap/app.php';
$app->useStoragePath($storagePath);

// 4. Handle Routing
$_SERVER['SCRIPT_NAME'] = '/index.php';
$_SERVER['SCRIPT_FILENAME'] = __DIR__ . '/../public/index.php';

if (isset($_SERVER['HTTP_X_FORWARDED_URI'])) {
    $_SERVER['REQUEST_URI'] = $_SERVER['HTTP_X_FORWARDED_URI'];
}

$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

$response->send();
$kernel->terminate($request, $response);