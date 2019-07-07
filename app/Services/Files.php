<?php

declare(strict_types = 1);

namespace App\Services;

class Files extends \Illuminate\Filesystem\Filesystem
{
    /**
     * @param string $path
     * @param int $mode
     * @param bool $recursive
     * @param bool $force
     * @return void
     */
    public function ensureDirExists(string $path, int $mode = 0755, bool $recursive = true, bool $force = true): void
    {
        if (!$this->isDirectory($path)) {
            $this->makeDirectory($path, $mode, $recursive, $force);
        }
    }

    /**
     * Determine if the given path is a symbolic link.
     *
     * @param string $path
     * @return bool
     */
    public function isLink(string $path): bool
    {
        return is_link($path);
    }

    /**
     * Resolve the given symbolic link.
     *
     * @param string $path
     * @return string|false
     */
    public function readLink(string $path)
    {
        return readlink($path);
    }

    /**
     * @param float $size
     * @return string
     */
    public function getFormatedFileSize(float $size): string
    {
        $arBytes = [
            [
                'tag' => 'GB',
                'value' => pow(1024, 3)
            ],
            [
                'tag' => 'MB',
                'value' => pow(1024, 2)
            ],
            [
                'tag' => 'KB',
                'value' => 1024
            ],
            [
                'tag' => 'B',
                'value' => 1
            ]
        ];

        $result = '0';
        foreach ($arBytes as $arItem) {
            if ($size >= $arItem['value']) {
                $result = $size / $arItem['value'];
                $result = str_replace('.', ',' , strval(round($result, 2))) . ' ' . $arItem['tag'];
                break;
            }
        }
        return $result;
    }

    /**
     * Read a file as a stream.
     *
     * @param string $filePath
     * @return resource|false
     */
    public function readStream(string $filePath)
    {
        return fopen($filePath, 'rb');
    }
}
