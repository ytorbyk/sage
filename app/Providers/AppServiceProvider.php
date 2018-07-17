<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use App\Command;
use App\Facades\Brew as BrewFacade;
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
    /**
     * @return void
     */
    public function boot(): void
    {
        Command::macro(
            'installFormula',
            function (string $formula) {
                /** @var Command $this */
                $needInstall = $this->task('Need to be installed?', function () use ($formula) {
                    return !BrewFacade::isInstalled($formula) ?: 'Installed. Skip';
                });

                if ($needInstall === true) {
                    /** @var Command $this */
                    $this->task(sprintf('Install [%s] Brew formula', $formula), function () use ($formula) {
                        BrewFacade::install($formula);
                    });
                }
                return $needInstall;
            }
        );

        Command::macro(
            'uninstallFormula',
            function (string $formula) {
                /** @var Command $this */
                $isInstalled = $this->task('Need to be uninstalled?', function () use ($formula) {
                    return BrewFacade::isInstalled($formula) ?: 'Uninstalled. Skip';
                });

                if ($isInstalled === true) {
                    /** @var Command $this */
                    $this->task(sprintf('Uninstall %s Brew formula', $formula), function () use ($formula) {
                        BrewFacade::uninstall($formula, ['--force']);
                    });
                }

                return $isInstalled;
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
    }
}
