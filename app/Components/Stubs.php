<?php

namespace App\Components;

class Stubs
{
    const STUB_DIR = 'stubs';

    /**
     * @var \App\Components\Files
     */
    private $files;

    /**
     * @param \App\Components\Files $files
     */
    public function __construct(
        \App\Components\Files $files
    ) {
        $this->files = $files;
    }

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
        return $this->files->exists($this->getPath($name));
    }

    /**
     * @param string $name
     * @param array $vars
     * @return string
     */
    public function get(string $name, array $vars = []): string
    {
        $content = $this->files->get($this->getPath($name));

        foreach ($vars as $name => $value) {
            $content = str_replace($name, $value, $content);
        }

        return $content;
    }
}
