<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static bool isInstalled(string $extension)
 * @method static bool isEnabled(string $extension)
 * @method static void enable(string $extension)
 * @method static void disable(string $extension)
 * @method static string iniPath(string $extension, bool $disabled = false)
 * @method static string getConfDPath()
 * @method static string getPhpIniPath()
 * @method static string getExtensionDirectory()
 * @method static void updatePeclChannel()
 * @method static void install(string $extension, string $phpVersion)
 * @method static void configure($extension)
 * @method static void uninstall(string $extension, string $phpVersion)
 * @method static void deleteConfigs(string $phpVersion)
 *
 * @see \App\Services\Pecl
 */
class PeclHelper extends Facade
{
    /**
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'pecl.helper';
    }
}
