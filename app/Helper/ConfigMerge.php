<?php

declare(strict_types = 1);

namespace App\Helper;

trait ConfigMerge
{
    /**
     * @param string $path
     * @param string $key
     * @return void
     */
    protected function mergeRecursiveConfigFromPath($path, $key)
    {
        $this->mergeRecursiveConfigFrom(require $path, $key);
    }

    /**
     * @param array $config
     * @param string $key
     * @return void
     */
    protected function mergeRecursiveConfigFrom(array $config, $key)
    {
        config([$key => array_replace_recursive($config, (array)config($key))]);
    }
}
