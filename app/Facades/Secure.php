<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static bool canSecure(string $domain)
 * @method static bool canGenerate(string $domain)
 * @method static bool hasPredefined(string $domain)
 * @method static void generate(string $domain, array $aliases = [])
 * @method static void delete(string $domain)
 * @method static string getFilePath(string $domain, string $fileType)
 *
 * @see \App\Services\Stubs
 */
class Secure extends Facade
{
    /**
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'secure';
    }
}
