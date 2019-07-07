<?php

declare(strict_types = 1);

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static bool isEnabled()
 * @method static bool isInstalled()
 * @method static void enable()
 * @method static void disable()
 * @method static void configure()
 *
 * @see \App\Commands\Php\SessionMemcachedCommand
 */
class SessionMemcached extends Facade
{
}
