<?php

namespace App\Commands\Php;

use LaravelZero\Framework\Commands\Command;
use App\Components\Site\Pecl;

class UninstallCommand extends Command
{
    const COMMAND = 'php:uninstall';

    /**
     * @var string
     */
    protected $signature = self::COMMAND;

    /**
     * @var string
     */
    protected $description = 'Uninstall PHP';

    /**
     * @var \App\Components\Brew
     */
    private $brew;

    /**
     * @var \App\Components\Brew\Service
     */
    private $brewService;

    /**
     * @var \App\Components\Site\Php
     */
    private $php;

    /**
     * @var \App\Components\Site\Pecl
     */
    private $pecl;

    /**
     * @var \App\Components\Site\IonCube
     */
    private $ionCube;

    /**
     * @var \App\Components\Files
     */
    private $files;

    /**
     * @param \App\Components\Brew $brew
     * @param \App\Components\Brew\Service $brewService
     * @param \App\Components\Site\Php $php
     * @param \App\Components\Site\Pecl $pecl
     * @param \App\Components\Site\IonCube $ionCube
     * @param \App\Components\Files $files
     */
    public function __construct(
        \App\Components\Brew $brew,
        \App\Components\Brew\Service $brewService,
        \App\Components\Site\Php $php,
        \App\Components\Site\Pecl $pecl,
        \App\Components\Site\IonCube $ionCube,
        \App\Components\Files $files
    ) {
        $this->brew = $brew;
        $this->brewService = $brewService;
        $this->php = $php;
        $this->pecl = $pecl;
        $this->ionCube = $ionCube;
        $this->files = $files;
        parent::__construct();
    }

    /**
     * @return void
     */
    public function handle(): void
    {
        $phpVersions = config('env.php.versions');
        foreach ($phpVersions as $phpVersion) {
            $this->info(sprintf('Uninstall PHP v%s', $phpVersion));
            $this->uninstallVersion($phpVersion);
        }

        $this->brew->ensureUninstalled('autoconf');

        $this->files->deleteDirectory(config('env.php.brew_etc_path'));
        $this->files->deleteDirectory(config('env.php.brew_lib_path'));
    }

    /**
     * @param string $phpVersion
     * @return void
     */
    private function uninstallVersion(string $phpVersion): void
    {
        $this->job('Ensure no PHP is linked', function () {
            $currentVersion = $this->php->getLinkedPhp();
            if ($currentVersion !== null) {
                $this->php->unlink($currentVersion);
            }
        });

        $isPhpInstalled = $this->job(sprintf('PHP v%s need to be uninstalled?', $phpVersion), function () use ($phpVersion) {
            return $this->brew->isInstalled($this->php->getFormula($phpVersion)) ?: 'Uninstalled. Skip';
        });
        if ($isPhpInstalled !== true) {
            return;
        }

        $this->job(sprintf('Link PHP v%s', $phpVersion), function () use ($phpVersion) {
            $this->php->link($phpVersion);
        });

        $this->uninstallPeclExtension($phpVersion, Pecl::APCU_EXTENSION);
        $this->uninstallPeclExtension($phpVersion, Pecl::XDEBUG_EXTENSION);

        $this->job('[ioncube] uninstall', function () {
            $this->ionCube->uninstall();
        });

        $this->job(sprintf('PHP v%s uninstall', $phpVersion), function () use ($phpVersion) {
            $this->brewService->stop($this->php->getFormula($phpVersion));
            $this->brew->uninstall($this->php->getFormula($phpVersion));
        });

        $this->job('PECL delete config', function () use ($phpVersion) {
            $this->pecl->deleteConfigs($phpVersion);
        });
    }

    /**
     * @param string $phpVersion
     * @param string $extension
     * @return void
     */
    private function uninstallPeclExtension(string $phpVersion, string $extension): void
    {
        $apcuInstalled = $this->job(sprintf('[%s] need to be uninstalled?', $extension), function () use ($extension) {
            return $this->pecl->isInstalled($extension) ?: 'Uninstalled. Skip';
        });
        if ($apcuInstalled === true) {
            $this->job(sprintf('[%s] uninstall ', $extension), function () use ($phpVersion, $extension) {
                $this->pecl->uninstall($extension, $phpVersion);
            });
        }
    }
}
