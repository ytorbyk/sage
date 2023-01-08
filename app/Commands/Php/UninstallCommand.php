<?php

declare(strict_types = 1);

namespace App\Commands\Php;

use App\Command;
use App\Facades\Brew;
use App\Facades\BrewService;
use App\Facades\PhpHelper;
use App\Facades\PeclHelper;
use App\Facades\IonCubeHelper;
use App\Facades\File;
use App\Services\Pecl;

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
     * @return void
     */
    public function handle(): void
    {
        $phpVersions = config('env.php.versions');
        foreach ($phpVersions as $phpVersion) {
            if ($phpVersion === '7.3') {
                continue;
            }

            $this->info(sprintf('Uninstall PHP v%s', $phpVersion));
            $this->uninstallVersion($phpVersion);
        }

//        foreach (config('env.php.dependencies') as $formula) {
//            Brew::ensureUninstalled($formula);
//        }

        File::deleteDirectory((string)config('env.php.brew_etc_path'));
        File::deleteDirectory((string)config('env.php.brew_lib_path'));
    }

    /**
     * @param string $phpVersion
     * @return void
     */
    private function uninstallVersion(string $phpVersion): void
    {
        $this->task('Ensure no PHP is linked', function () {
            $currentVersion = PhpHelper::getLinkedPhp();
            if ($currentVersion !== null) {
                PhpHelper::unlink($currentVersion);
            }
        });

        $isPhpInstalled = $this->task(sprintf('Need to be uninstalled PHP v%s?', $phpVersion), function () use ($phpVersion) {
            return Brew::isInstalled(PhpHelper::getFormula($phpVersion)) ?: 'Uninstalled. Skip';
        });
        if ($isPhpInstalled !== true) {
            return;
        }

        $this->task(sprintf('Link PHP v%s', $phpVersion), function () use ($phpVersion) {
            PhpHelper::link($phpVersion);
        });

        $this->uninstallPeclExtension($phpVersion, Pecl::XDEBUG_EXTENSION);

        if ($phpVersion !== '5.6') {
            $this->uninstallPeclExtension($phpVersion, Pecl::IMAGICK_EXTENSION);
            $this->uninstallPeclExtension($phpVersion, Pecl::MEMCACHED_EXTENSION);
        }

        $this->task('[ioncube] uninstall', function () {
            try {
                IonCubeHelper::uninstall();
            } catch (\Exception $e) {
                return $e->getMessage();
            }
        });

        $this->task(sprintf('Uninstall %s Brew formula', PhpHelper::getFormula($phpVersion)), function () use ($phpVersion) {
            BrewService::stop(PhpHelper::getFormula($phpVersion));
            Brew::uninstall(PhpHelper::getFormula($phpVersion), ['--force']);
        });

        $this->task('PECL delete config', function () use ($phpVersion) {
            PeclHelper::deleteConfigs($phpVersion);
        });
    }

    /**
     * @param string $phpVersion
     * @param string $extension
     * @return void
     */
    private function uninstallPeclExtension(string $phpVersion, string $extension): void
    {
        $apcuInstalled = $this->task(sprintf('[%s] need to be uninstalled?', $extension), function () use ($extension) {
            return PeclHelper::isInstalled($extension) ?: 'Uninstalled. Skip';
        });
        if ($apcuInstalled === true) {
            $this->task(sprintf('[%s] uninstall', $extension), function () use ($phpVersion, $extension) {
                try {
                    PeclHelper::uninstall($extension, $phpVersion);
                } catch (\Exception $e) {
                    return $e->getMessage();
                }
            });
        }
    }
}
