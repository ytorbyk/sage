<?php

if (version_compare(PHP_VERSION, '7.1.3') < 0) {
    echo 'PHP 7.1.3 is the minimal supported version.' . PHP_EOL;
    exit(0);
}

ini_set('max_execution_time', 0);


$app = include 'app.php';

$homeFolder = '.' . mb_strtolower($app['name']);

$env = [
    'home' => env('HOME') . DIRECTORY_SEPARATOR . $homeFolder,
    'home_public' => env('HOME') . DIRECTORY_SEPARATOR .'x' . $app['name'],
    'config_path' => env('HOME') . DIRECTORY_SEPARATOR . $homeFolder . DIRECTORY_SEPARATOR . 'config.php',
];

if (file_exists($env['config_path'])) {
    $localConfig = include $env['config_path'];
    if (!empty($localConfig) && is_array($localConfig)) {
        return $env + $localConfig;
    }
}
return $env;
