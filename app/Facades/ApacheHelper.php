<?php

declare(strict_types = 1);

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static void configure()
 * @method static void initDefaultLocalhostVHost()
 * @method static void configureVHost(string $domain, string $documentRoot, array $aliases = [], bool $secure = true)
 * @method static void configureProxyVHost(string $domain, string $port, array $aliases = [], bool $secure = true)
 * @method static void deleteVHost(string $domain)
 * @method static void linkPhp(string $version)
 * @method static void unlinkPhp()
 *
 * @see \App\Services\Apache
 */
class ApacheHelper extends Facade
{
    /**
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'apache.helper';
    }
}
