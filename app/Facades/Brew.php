<?php

declare(strict_types = 1);

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static bool isBrewAvailable()
 * @method static string link(string $formula)
 * @method static string unlink(string $formula)
 * @method static bool isInstalled(string $formula)
 * @method static bool ensureInstalled(string $formula, array $options = [], array $taps = [])
 * @method static string install(string $formula, array $options = [], array $taps = null)
 * @method static bool ensureUninstalled(string $formula, array $options = [])
 * @method static string uninstall(string $formula, array $options = [])
 * @method static void tap(string ... $formulas)
 * @method static void unTap(string ... $formulas)
 * @method static bool hasTap(string $formula)
 *
 * @see \App\Services\Brew
 */
class Brew extends Facade
{
    /**
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'brew';
    }
}
