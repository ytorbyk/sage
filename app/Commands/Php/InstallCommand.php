<?php

declare(strict_types = 1);

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
use App\Facades\MemcachedSession;
use App\Commands\Memcached\InstallCommand as MemcachedInstallCommand;
use App\Commands\Memcached\StartCommand as MemcachedStartCommand;
use App\Commands\Memcached\SessionCommand as MemcachedSessionCommand;

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
     * @var array
     */
    protected $supportedSmtpCatchers = ['files', 'mailhog'];

    /**
     * @var array
     */
    protected $skipExtension = [
        '5.6' => [Pecl::IMAGICK_EXTENSION, Pecl::REDIS_EXTENSION, Pecl::MEMCACHED_EXTENSION],
        '8.0' => [Pecl::IMAGICK_EXTENSION]
    ];

    /**
     * @return void
     */
    public function handle(): void
    {
        $this->call(MemcachedInstallCommand::COMMAND);

        $phpVersions = config('env.php.versions');

        foreach (config('env.php.dependencies') as $formula) {
            Brew::ensureInstalled($formula);
        }

        $this->setupSmtpCatcher();

        foreach ($phpVersions as $phpVersion) {
            $this->info(sprintf('Install PHP v%s', $phpVersion));
            $this->installVersion($phpVersion);
        }

        $this->call(MemcachedStartCommand::COMMAND);

        File::deleteDirectory((string)config('env.tmp_path'));
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

        $phpTaps = config('env.php.taps');
        $taps = !empty($phpTaps[$phpVersion]) ? $phpTaps[$phpVersion] : null;

        $installOptions = config('env.php.install_options');
        $options = !empty($installOptions[$phpVersion]) ? $installOptions[$phpVersion] : [];

        if ($this->installFormula(PhpHelper::getFormula($phpVersion), $options, $taps)) {
            BrewService::stop(PhpHelper::getFormula($phpVersion));
        }

        $this->task(sprintf('PHP v%s link', $phpVersion), function () use ($phpVersion) {
            PhpHelper::link($phpVersion);
        });

        $this->task(sprintf('PHP v%s update ini files', $phpVersion), function () use ($phpVersion) {
            $this->tunePhpIni($phpVersion);
            $this->tuneOpCache();
        });

        $this->task('PECL updating channel', function () {
            PeclHelper::updatePeclChannel();
        });

        $this->installPeclExtension($phpVersion, Pecl::XDEBUG_EXTENSION);

        if (empty($this->skipExtension[$phpVersion])
            || !in_array(Pecl::IMAGICK_EXTENSION, $this->skipExtension[$phpVersion], true)
        ) {
            $this->installPeclExtension($phpVersion, Pecl::IMAGICK_EXTENSION);
        }

        if (empty($this->skipExtension[$phpVersion])
            || !in_array(Pecl::REDIS_EXTENSION, $this->skipExtension[$phpVersion], true)
        ) {
            $this->installPeclExtension($phpVersion, Pecl::REDIS_EXTENSION);
        }

        if (empty($this->skipExtension[$phpVersion])
            || !in_array(Pecl::MEMCACHED_EXTENSION, $this->skipExtension[$phpVersion], true)
        ) {
            $this->installPeclExtension($phpVersion, Pecl::MEMCACHED_EXTENSION);
            MemcachedSession::configure();
        }

        $this->installIonCube($phpVersion);

        $this->call(MemcachedSessionCommand::COMMAND, ['action' => 'off', '--skip' => 1]);
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
        $needInstall = $this->task(sprintf('[%s] need to be installed?', $extension), function () use ($phpVersion, $extension) {
            return !PeclHelper::isInstalled($extension) ?: 'Installed. Skip';
        });
        if ($needInstall === true) {
            $this->task(sprintf('[%s] install', $extension), function () use ($phpVersion, $extension) {
                PeclHelper::install($extension, $phpVersion);
            });
        }

        $this->task(sprintf('[%s] configure', $extension), function () use ($phpVersion, $extension) {
            PeclHelper::configure($extension, $phpVersion);
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

        $ioncubeNeedConfigure = true;
        if ($ioncubeNeedInstall === true) {
            $ioncubeNeedConfigure = $this->task('[ioncube] install', function () use ($phpVersion) {
                try {
                    IonCubeHelper::install($phpVersion);
                } catch (\Exception $e) {
                    return $e->getMessage();
                }
                return true;
            });
        }

        if ($ioncubeNeedConfigure === true) {
            $this->task('[ioncube] configure', function () {
                IonCubeHelper::configure();
            });
        }
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
    private function tunePhpIni(string $phpVersion)
    {
        File::ensureDirExists(PeclHelper::getConfdPath());

        $smtpCatcher = config('env.php.smtp_catcher');
        $smtpCatcher = in_array($smtpCatcher, $this->supportedSmtpCatchers, true) ? $smtpCatcher : 'files';

        $phpZIni = Stub::get('php/z-performance.ini', [
            'TIMEZONE' => $this->getSystemTimeZone(),
            'SMTP_CATCHER_PATH' => config('env.php.smtp_catcher_' . $smtpCatcher),
            'ERROR_LOG_FILE' => config('env.home_public') . DIRECTORY_SEPARATOR . 'logs' . DIRECTORY_SEPARATOR . 'php' . $phpVersion . '-error.log'
        ]);
        File::put(PeclHelper::getConfdPath() . 'z-performance.ini', $phpZIni);
    }

    /**
     * @return void
     */
    private function setupSmtpCatcher()
    {
        $mailDir = (string)config('env.php.mail_path');
        $smtpCatcherPath = (string)config('env.php.smtp_catcher_files');
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
