<?php

declare(strict_types = 1);

namespace App\Facades;

use Illuminate\Support\Facades\File as SupportFile;

/**
 * @method static void ensureDirExists(string $path, int $mode = 0755, bool $recursive = true, bool $force = true)
 * @method static bool isLink(string $path)
 * @method static string|bool readLink(string $path)
 * @method static string getFormatedFileSize(float $size)
 * @method static array readStream(string $filePath)
 *
 * @see \App\Services\Files
 */
class File extends SupportFile
{
    /**
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'files';
    }
}
