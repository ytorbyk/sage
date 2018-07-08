<?php

namespace App\Components\Site;

class Php
{
    /**
     * @var \App\Components\Files
     */
    private $files;

    /**
     * @var \App\Components\Brew
     */
    private $brew;

    /**
     * @param \App\Components\Files $files
     * @param \App\Components\Brew $brew
     */
    public function __construct(
        \App\Components\Files $files,
        \App\Components\Brew $brew
    ) {
        $this->files = $files;
        $this->brew = $brew;
    }

    /**
     * @param string $version
     * @return void
     */
    public function switchTo(string $version): void
    {
        $currentVersion = $this->getLinkedPhp();
        if ($currentVersion) {
            $this->brew->unlink($this->getFormula($currentVersion));
        }

        $this->brew->link($this->getFormula($version));
    }

    /**
     * @return null|string
     */
    public function getLinkedPhp(): ?string
    {
        if (!$this->files->isLink(config('env.php.brew_path'))) {
            return null;
        }

        $resolvedPath = $this->files->readLink(config('env.php.brew_path'));

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
        $this->brew->link($this->getFormula($version));
    }

    /**
     * @param string $version
     * @return void
     */
    public function unlink(string $version): void
    {
        $this->brew->unlink($this->getFormula($version));
    }

    /**
     * @param string $version
     * @return string
     */
    public function getFormula($version)
    {
        return config('env.php.main_version') === $version ? 'php' : 'php@' . $version;
    }
}
