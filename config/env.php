<?php

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
