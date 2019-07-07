<?php

declare(strict_types = 1);

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static bool isStarted(string $service)
 * @method static bool[] getServicesStatus()
 * @method static bool start(string $service, bool $root = false)
 * @method static void stop(string $service)
 * @method static void restart(string $service, bool $root = false)
 *
 * @see \App\Services\BrewService
 */
class BrewService extends Facade
{
    /**
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'brew.service';
    }
}
