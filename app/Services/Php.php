<?php

declare(strict_types = 1);

namespace App\Services;

use App\Facades\Brew;
use App\Facades\File;

class Php
{
    /**
     * @param string $version
     * @return void
     */
    public function switchTo(string $version): void
    {
        $currentVersion = $this->getLinkedPhp();
        if ($currentVersion) {
            Brew::unlink($this->getFormula($currentVersion));
        }

        Brew::link($this->getFormula($version));
    }

    /**
     * @return null|string
     */
    public function getLinkedPhp(): ?string
    {
        if (!File::isLink((string)config('env.php.brew_path'))) {
            return null;
        }

        $resolvedPath = File::readLink((string)config('env.php.brew_path'));

        foreach (config('env.php.versions') as $phpVersion) {
            if (strpos($resolvedPath, $phpVersion) !== false) {
                return $phpVersion;
            }
        }

        throw new \DomainException('Unable to determine linked PHP.');
    }

    /**
     * @param string $version
     * @return void
     */
    public function link(string $version): void
    {
        Brew::link($this->getFormula($version));
    }

    /**
     * @param string $version
     * @return void
     */
    public function unlink(string $version): void
    {
        Brew::unlink($this->getFormula($version));
    }

    /**
     * @param string $version
     * @return string
     */
    public function getFormula(string $version): string
    {
        return config('env.php.main_version') === $version ? 'php' : 'php@' . $version;
    }
}
