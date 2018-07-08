<?php

namespace App\Commands\Php;

use LaravelZero\Framework\Commands\Command;
use App\Components\Site\Pecl;

class InstallCommand extends Command
{
    const COMMAND = 'php:install';

    /**
     * @var string
     */
    protected $signature = self::COMMAND;

    /**
     * @var string
     */
    protected $description = 'Install and configure PHP';

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
     * @var \App\Components\Stubs
     */
    private $stubs;

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
     * @param \App\Components\Stubs $stubs
     * @param \App\Components\Files $files
     */
    public function __construct(
        \App\Components\Brew $brew,
        \App\Components\Brew\Service $brewService,
        \App\Components\Site\Php $php,
        \App\Components\Site\Pecl $pecl,
        \App\Components\Site\IonCube $ionCube,
        \App\Components\Stubs $stubs,
        \App\Components\Files $files
    ) {
        $this->brew = $brew;
        $this->brewService = $brewService;
        $this->php = $php;
        $this->pecl = $pecl;
        $this->ionCube = $ionCube;
        $this->stubs = $stubs;
        $this->files = $files;
        parent::__construct();
    }

    /**
     * @return void
     */
    public function handle(): void
    {
        $phpVersions = config('env.php.versions');

        $this->brew->ensureInstalled('autoconf');
        $this->setupSmtpCatcher();

        foreach ($phpVersions as $phpVersion) {
            $this->info(sprintf('Install PHP v%s', $phpVersion));
            $this->installVersion($phpVersion);
        }

        $this->files->deleteDirectory(config('env.tmp_path'));
    }

    /**
     * @param string $phpVersion
     * @return void
     */
    private function installVersion(string $phpVersion): void
    {
        $this->job('Ensure no PHP is linked', function () {
            $currentVersion = $this->php->getLinkedPhp();
            if ($currentVersion !== null) {
                $this->php->unlink($currentVersion);
            }
        });


        $phpNeedInstall = $this->job(sprintf('PHP v%s need to be installed?', $phpVersion), function () use ($phpVersion) {
            return !$this->brew->isInstalled($this->php->getFormula($phpVersion)) ?: 'Installed. Skip';
        });
        if ($phpNeedInstall === true) {
            $this->job(sprintf('PHP v%s install', $phpVersion), function () use ($phpVersion) {
                $this->brew->install($this->php->getFormula($phpVersion));
                $this->brewService->stop($this->php->getFormula($phpVersion));
            });
        }

        $this->job(sprintf('PHP v%s link ', $phpVersion), function () use ($phpVersion) {
            $this->php->link($phpVersion);
        });

        $this->job(sprintf('PHP v%s update ini files', $phpVersion), function () use ($phpVersion) {
            $this->tunePhpIni();
            $this->tuneOpCache();
        });

        $this->job('PECL updating channel', function () {
            $this->pecl->updatePeclChannel();
        });
        $this->installPeclExtension($phpVersion, Pecl::APCU_EXTENSION);
        $this->installPeclExtension($phpVersion, Pecl::XDEBUG_EXTENSION);

        $this->installIonCube($phpVersion);

        $this->call(IoncubeCommand::COMMAND, ['action' => 'off', '--skip' => 1]);
        $this->call(XdebugCommand::COMMAND, ['action' => 'off', '--skip' => 1]);
    }

    /**
     * @param string $phpVersion
     * @param string $extension
     * @return void
     */
    private function installPeclExtension(string $phpVersion, string $extension): void
    {
        $apcuNeedInstall = $this->job(sprintf('[%s] need to be installed?', $extension), function () use ($phpVersion, $extension) {
            return !$this->pecl->isInstalled($extension) ?: 'Installed. Skip';
        });
        if ($apcuNeedInstall === true) {
            $this->job(sprintf('[%s] install ', $extension), function () use ($phpVersion, $extension) {
                $this->pecl->install($extension, $phpVersion);
            });
        }

        $this->job(sprintf('[%s] configure', $extension), function () use ($extension) {
            $this->pecl->configure($extension);
        });
    }

    /**
     * @param string $phpVersion
     * @return void
     */
    private function installIonCube(string $phpVersion): void
    {
        $ioncubeNeedInstall = $this->job('[ioncube] need to be installed?', function () use ($phpVersion) {
            return !$this->ionCube->isInstalled() ?: 'Installed. Skip';
        });
        if ($ioncubeNeedInstall === true) {
            $this->job('[ioncube] install', function () use ($phpVersion) {
                $this->ionCube->install($phpVersion);
            });
        }
        $this->job('[ioncube] configure', function () {
            $this->ionCube->configure();
        });
    }

    /**
     * @return void
     */
    private function tuneOpCache()
    {
        if (!$this->files->exists($this->pecl->getConfdPath() . 'ext-opcache.ini.origin')) {
            $this->files->move(
                $this->pecl->getConfdPath() . 'ext-opcache.ini',
                $this->pecl->getConfdPath() . 'ext-opcache.ini.origin'
            );
        }
        $originOpCacheConfig = $this->files->get($this->pecl->getConfdPath() . 'ext-opcache.ini.origin') . PHP_EOL;
        $this->files->put(
            $this->pecl->getConfdPath() . 'ext-opcache.ini',
            $originOpCacheConfig . $this->stubs->get('php/ext-opcache.ini')
        );
    }

    /**
     * @return void
     */
    private function tunePhpIni()
    {
        $this->files->ensureDirExists($this->pecl->getConfdPath());

        $phpZIni = $this->stubs->get('php/z-performance.ini', [
            'TIMEZONE' => $this->getSystemTimeZone(),
            'SMTP_CATCHER_PATH' => config('env.php.smtp_catcher_path')
        ]);
        $this->files->put($this->pecl->getConfdPath() . 'z-performance.ini', $phpZIni);
    }

    /**
     * @return void
     */
    private function setupSmtpCatcher()
    {
        $mailDir = config('env.php.mail_path');
        $smtpCatcherPath = config('env.php.smtp_catcher_path');
        $this->files->ensureDirExists($mailDir);

        $smtpCatcher = $this->stubs->get('php/smtp_catcher.php', [
            'TIMEZONE' => $this->getSystemTimeZone(),
            'MAIL_FOLDER' => $mailDir
        ]);
        $this->files->put($smtpCatcherPath, $smtpCatcher);
        $this->files->chmod($smtpCatcherPath, 0755);
    }

    /**
     * @return string
     */
    private function getSystemTimeZone()
    {
        $systemZoneName = readlink('/etc/localtime');
        // All versions below High Sierra
        $systemZoneName = str_replace('/usr/share/zoneinfo/', '', $systemZoneName);
        // macOS High Sierra has a new location for the timezone info
        $systemZoneName = str_replace('/var/db/timezone/zoneinfo/', '', $systemZoneName);

        return $systemZoneName;
    }
}
