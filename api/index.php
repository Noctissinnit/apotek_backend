<?php

/*
|--------------------------------------------------------------------------
| Entry point untuk Vercel
|--------------------------------------------------------------------------
| Semua request diarahkan ke file ini lewat "routes" di vercel.json.
|
| Filesystem serverless bersifat read-only kecuali /tmp, jadi semua path
| yang ditulisi Laravel (storage + bootstrap/cache) dipindah ke /tmp
| SEBELUM framework di-boot. Laravel membaca LARAVEL_STORAGE_PATH dan
| variabel APP_*_CACHE langsung dari $_ENV/$_SERVER.
*/

$tmp = '/tmp';

$folder = [
    $tmp.'/bootstrap/cache',
    $tmp.'/storage/app/public',
    $tmp.'/storage/framework/cache/data',
    $tmp.'/storage/framework/sessions',
    $tmp.'/storage/framework/views',
    $tmp.'/storage/logs',
];

foreach ($folder as $path) {
    if (! is_dir($path)) {
        @mkdir($path, 0755, true);
    }
}

$overrides = [
    'LARAVEL_STORAGE_PATH' => $tmp.'/storage',
    'VIEW_COMPILED_PATH' => $tmp.'/storage/framework/views',
    'APP_CONFIG_CACHE' => $tmp.'/bootstrap/cache/config.php',
    'APP_EVENTS_CACHE' => $tmp.'/bootstrap/cache/events.php',
    'APP_PACKAGES_CACHE' => $tmp.'/bootstrap/cache/packages.php',
    'APP_ROUTES_CACHE' => $tmp.'/bootstrap/cache/routes.php',
    'APP_SERVICES_CACHE' => $tmp.'/bootstrap/cache/services.php',
];

foreach ($overrides as $key => $value) {
    $_ENV[$key] = $value;
    $_SERVER[$key] = $value;
    putenv($key.'='.$value);
}

require __DIR__.'/../public/index.php';
