<?php

namespace App\Commands\Php;

use App\Command;
use App\Facades\Brew;
use App\Facades\BrewService;
use App\Facades\PhpHelper;
use App\Facades\PeclHelper;
use App\Facades\IonCubeHelper;
use App\Facades\Stub;
use App\Facades\File;
use App\Services\Pecl;

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
     * @return void
     */
    public function handle(): void
    {
        $phpVersions = config('env.php.versions');

        Brew::ensureInstalled('autoconf');
        $this->setupSmtpCatcher();

        foreach ($phpVersions as $phpVersion) {
            $this->info(sprintf('Install PHP v%s', $phpVersion));
            $this->installVersion($phpVersion);
        }

        File::deleteDirectory(config('env.tmp_path'));
    }

    /**
     * @param string $phpVersion
     * @return void
     */
    private function installVersion(string $phpVersion): void
    {
        $this->task('Ensure no PHP is linked', function () {
            $currentVersion = PhpHelper::getLinkedPhp();
            if ($currentVersion !== null) {
                PhpHelper::unlink($currentVersion);
            }
        });

        if ($this->installFormula(PhpHelper::getFormula($phpVersion))) {
            BrewService::stop(PhpHelper::getFormula($phpVersion));
        }

        $this->task(sprintf('PHP v%s link', $phpVersion), function () use ($phpVersion) {
            PhpHelper::link($phpVersion);
        });

        $this->task(sprintf('PHP v%s update ini files', $phpVersion), function () use ($phpVersion) {
            $this->tunePhpIni();
            $this->tuneOpCache();
        });

        $this->task('PECL updating channel', function () {
            PeclHelper::updatePeclChannel();
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
        $apcuNeedInstall = $this->task(sprintf('[%s] need to be installed?', $extension), function () use ($phpVersion, $extension) {
            return !PeclHelper::isInstalled($extension) ?: 'Installed. Skip';
        });
        if ($apcuNeedInstall === true) {
            $this->task(sprintf('[%s] install', $extension), function () use ($phpVersion, $extension) {
                PeclHelper::install($extension, $phpVersion);
            });
        }

        $this->task(sprintf('[%s] configure', $extension), function () use ($extension) {
            PeclHelper::configure($extension);
        });
    }

    /**
     * @param string $phpVersion
     * @return void
     */
    private function installIonCube(string $phpVersion): void
    {
        $ioncubeNeedInstall = $this->task('[ioncube] need to be installed?', function () use ($phpVersion) {
            return !IonCubeHelper::isInstalled() ?: 'Installed. Skip';
        });
        if ($ioncubeNeedInstall === true) {
            $this->task('[ioncube] install', function () use ($phpVersion) {
                IonCubeHelper::install($phpVersion);
            });
        }
        $this->task('[ioncube] configure', function () {
            IonCubeHelper::configure();
        });
    }

    /**
     * @return void
     */
    private function tuneOpCache()
    {
        if (!File::exists(PeclHelper::getConfdPath() . 'ext-opcache.ini.origin')) {
            File::move(
                PeclHelper::getConfdPath() . 'ext-opcache.ini',
                PeclHelper::getConfdPath() . 'ext-opcache.ini.origin'
            );
        }
        $originOpCacheConfig = File::get(PeclHelper::getConfdPath() . 'ext-opcache.ini.origin') . PHP_EOL;
        File::put(
            PeclHelper::getConfdPath() . 'ext-opcache.ini',
            $originOpCacheConfig . Stub::get('php/ext-opcache.ini')
        );
    }

    /**
     * @return void
     */
    private function tunePhpIni()
    {
        File::ensureDirExists(PeclHelper::getConfdPath());

        $phpZIni = Stub::get('php/z-performance.ini', [
            'TIMEZONE' => $this->getSystemTimeZone(),
            'SMTP_CATCHER_PATH' => config('env.php.smtp_catcher_path')
        ]);
        File::put(PeclHelper::getConfdPath() . 'z-performance.ini', $phpZIni);
    }

    /**
     * @return void
     */
    private function setupSmtpCatcher()
    {
        $mailDir = config('env.php.mail_path');
        $smtpCatcherPath = config('env.php.smtp_catcher_path');
        File::ensureDirExists($mailDir);

        $smtpCatcher = Stub::get('php/smtp_catcher.php', [
            'TIMEZONE' => $this->getSystemTimeZone(),
            'MAIL_FOLDER' => $mailDir
        ]);
        File::put($smtpCatcherPath, $smtpCatcher);
        File::chmod($smtpCatcherPath, 0755);
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
