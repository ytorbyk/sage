<?php

declare(strict_types = 1);

namespace App\Services;

use App\Facades\File;
use App\Facades\PeclHelper;
use App\Facades\Stub;

class MemcachedSession
{
    /**
     * @return bool
     */
    public function isEnabled(): bool
    {
        return File::exists($this->getConfigIniFilePath());
    }

    /**
     * @return bool
     */
    public function isInstalled(): bool
    {
        return PeclHelper::isInstalled(Pecl::MEMCACHED_EXTENSION);
    }

    /**
     * @return void
     */
    public function enable(): void
    {
        if (!File::exists($this->getConfigIniFilePath(true))) {
            throw new \RuntimeException('Memcached session config file is not found.');
        }
        File::move($this->getConfigIniFilePath(true), $this->getConfigIniFilePath());
    }

    /**
     * @return void
     */
    public function disable(): void
    {
        if (!File::exists($this->getConfigIniFilePath())) {
            throw new \RuntimeException('Memcached session config file is not found.');
        }
        File::move($this->getConfigIniFilePath(), $this->getConfigIniFilePath(true));
    }

    /**
     * @return void
     */
    public function configure(): void
    {
        File::put(PeclHelper::getConfDPath() . 'z-session-memcached.ini', Stub::get('php/z-session-memcached.ini'));
    }

    /**
     * @param bool $disabled
     * @return string
     */
    private function getConfigIniFilePath(bool $disabled = false): string
    {
        return PeclHelper::getConfDPath() . 'z-session-memcached.ini' . ($disabled ? '.disabled' : '');
    }
}
