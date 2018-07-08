<?php

namespace App\Components;

class Files extends \Illuminate\Filesystem\Filesystem
{
    /**
     * @param string $path
     * @param int $mode
     * @param bool $recursive
     * @param bool $force
     * @return void
     */
    public function ensureDirExists(string $path, int $mode = 0755, bool $recursive = true, bool $force = true)
    {
        if (!$this->isDirectory($path)) {
            $this->makeDirectory($path, $mode, $recursive, $force);
        }
    }

    /**
     * Determine if the given path is a symbolic link.
     *
     * @param  string  $path
     * @return bool
     */
    public function isLink($path)
    {
        return is_link($path);
    }

    /**
     * Resolve the given symbolic link.
     *
     * @param  string  $path
     * @return string
     */
    public function readLink($path)
    {
        return readlink($path);
    }
}
