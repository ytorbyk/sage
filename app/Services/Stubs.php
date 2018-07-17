<?php

namespace App\Services;

use App\Facades\File;

class Stubs
{
    protected const STUB_DIR = 'stubs';

    /**
     * @param string $name
     * @return string
     */
    public function getPath(string $name): string
    {
        return app_path(self::STUB_DIR) . DIRECTORY_SEPARATOR . $name;
    }

    /**
     * @param string $name
     * @return bool
     */
    public function isExist(string $name): bool
    {
        return File::exists($this->getPath($name));
    }

    /**
     * @param string $name
     * @param array $vars
     * @return string
     */
    public function get(string $name, array $vars = []): string
    {
        $content = File::get($this->getPath($name));

        foreach ($vars as $name => $value) {
            $content = str_replace($name, $value, $content);
        }

        return $content;
    }
}
