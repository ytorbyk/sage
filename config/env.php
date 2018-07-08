<?php

$app = include 'app.php';

$homeFolder = '.' . mb_strtolower($app['name']);

$env = [
    'home' => env('HOME') . DIRECTORY_SEPARATOR . $homeFolder,
    'home_config' => env('HOME') . DIRECTORY_SEPARATOR . $homeFolder . DIRECTORY_SEPARATOR . 'config.php',
];

if (file_exists($env['home_config'])) {
    $localConfig = include $env['home_config'];
    if (!empty($localConfig) && is_array($localConfig)) {
        return $env + $localConfig;
    }
}
return $env;
