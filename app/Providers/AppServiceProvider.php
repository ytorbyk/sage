<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use App\Command;
use App\Helper\ConfigMerge;
use App\Facades\Brew as BrewFacade;
use App\Facades\Stub as BrewStubs;
use App\Facades\File as FileStubs;

use App\Services\Files;
use App\Services\Stubs;
use App\Services\CommandLine;
use App\Services\Brew;
use App\Services\BrewService;
use App\Services\Secure;
use App\Services\Apache;
use App\Services\Php;
use App\Services\Pecl;
use App\Services\IonCube;

class AppServiceProvider extends ServiceProvider
{
    use ConfigMerge;

    /**
     * @return void
     */
    public function boot(): void
    {
        Command::macro(
            'installFormula',
            function (string $formula, array $options = [], string $tap = null) {
                /** @var Command $this */
                $needInstall = $this->task(sprintf('Does [%s] need to be installed?', $formula), function () use ($formula) {
                    return !BrewFacade::isInstalled($formula) ?: 'Installed. Skip';
                });

                if ($needInstall === true) {
                    /** @var Command $this */
                    $this->task(sprintf('Install [%s] Brew formula', $formula), function () use ($formula, $options, $tap) {
                        BrewFacade::install($formula, $options, (array)$tap);
                    });
                }
                return $needInstall === true;
            }
        );

        Command::macro(
            'uninstallFormula',
            function (string $formula) {
                /** @var Command $this */
                $isInstalled = $this->task(sprintf('Does [%s] need to be uninstalled?', $formula), function () use ($formula) {
                    return BrewFacade::isInstalled($formula) ?: 'Uninstalled. Skip';
                });

                if ($isInstalled === true) {
                    /** @var Command $this */
                    $this->task(sprintf('Uninstall [%s] Brew formula', $formula), function () use ($formula) {
                        BrewFacade::uninstall($formula, ['--force']);
                    });
                }

                return $isInstalled === true;
            }
        );

        Command::macro(
            'getCurrentPath',
            function (?string $path): string {
                if (null === $path) {
                    $hostPath = getcwd();
                } elseif (strpos($path, '/') === 0) {
                    $hostPath = $path;
                } else {
                    $hostPath = getcwd() . DIRECTORY_SEPARATOR . ltrim($path, DIRECTORY_SEPARATOR);
                }

                return $hostPath;
            }
        );

        Command::macro(
            'getFilePath',
            function (string $file, string $defaultRoot): string {
                if (strpos($file, '/') === 0) {
                    $path = $file;
                } elseif (strpos($file, './') === 0) {
                    $path = getcwd() . DIRECTORY_SEPARATOR . substr($file, 2);
                } else {
                    $path = $defaultRoot . DIRECTORY_SEPARATOR . $file;
                }

                return $path;
            }
        );

        Command::macro(
            'verifyPath',
            function (string $path, bool $isFile = true): bool {
                return FileStubs::exists($path)
                    && ($isFile && FileStubs::isFile($path)) || (!$isFile && FileStubs::isDirectory($path));
            }
        );
    }

    /**
     * @return void
     */
    public function register(): void
    {
        $this->app->extend('files', function () {
            return new Files;
        });

        $this->app->bind('stubs', function () {
            return new Stubs;
        });

        $this->app->bind('command-line', function () {
            return new CommandLine;
        });

        $this->app->bind('brew', function () {
            return new Brew;
        });

        $this->app->bind('brew.service', function () {
            return new BrewService;
        });

        $this->app->bind('secure', function () {
            return new Secure;
        });

        $this->app->bind('apache.helper', function () {
            return new Apache;
        });

        $this->app->bind('php.helper', function () {
            return new Php;
        });

        $this->app->bind('pecl.helper', function () {
            return new Pecl;
        });

        $this->app->bind('ioncube.helper', function () {
            return new IonCube;
        });

        $this->mergeRecursiveConfigFromPath(BrewStubs::getPath('config/env.php'), 'env');
        $this->mergeRecursiveConfigFromPath(BrewStubs::getPath('config/filesystems.php'), 'env.filesystems');
        $this->mergeRecursiveConfigFrom(config('env.filesystems'), 'filesystems');

    }
}
