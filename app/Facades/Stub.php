<?php

declare(strict_types = 1);

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static string getPath(string $name)
 * @method static bool isExist(string $name)
 * @method static string get(string $name, array $vars = [])
 *
 * @see \App\Services\Stubs
 */
class Stub extends Facade
{
    /**
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'stubs';
    }
}
