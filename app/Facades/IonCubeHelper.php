<?php

declare(strict_types = 1);

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static bool isInstalled()
 * @method static bool isEnabled()
 * @method static void enable()
 * @method static void disable()
 * @method static void configure()
 * @method static void install(string $phpVersion)
 * @method static void uninstall()
 *
 * @see \App\Services\IonCube
 */
class IonCubeHelper extends Facade
{
    /**
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'ioncube.helper';
    }
}
