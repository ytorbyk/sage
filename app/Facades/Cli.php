<?php

declare(strict_types = 1);

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static string run(string $command)
 * @method static string runQuietly(string $command)
 * @method static void passthru(string $command)
 *
 * @see \App\Services\CommandLine
 */
class Cli extends Facade
{
    /**
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'command-line';
    }
}
