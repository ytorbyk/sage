<?php

declare(strict_types = 1);

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static string getLinkedPhp()
 * @method static void link(string $version)
 * @method static void unlink(string $version)
 * @method static void switchTo(string $version)
 * @method static string getFormula(string $version)
 *
 * @see \App\Services\Php
 */
class PhpHelper extends Facade
{
    /**
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'php.helper';
    }
}
