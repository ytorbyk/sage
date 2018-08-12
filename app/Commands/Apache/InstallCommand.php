<?php

namespace App\Commands\Apache;

use App\Command;
use App\Facades\Brew;
use App\Facades\BrewService;
use App\Facades\Cli;
use App\Facades\File;
use App\Facades\ApacheHelper;

class InstallCommand extends Command
{
    const COMMAND = 'apache:install';

    /**
     * @var string
     */
    protected $signature = self::COMMAND;

    /**
     * @var string
     */
    protected $description = 'Install and configure Apache';

    /**
     * @return void
     */
    public function handle(): void
    {
        $this->info('Install Apache:');

        $this->task('Ensure Apache is not running', function () {
            Cli::runQuietly('sudo apachectl stop');
            Cli::runQuietly('sudo launchctl unload -w /System/Library/LaunchDaemons/org.apache.httpd.plist');

            if (Brew::isInstalled(config('env.apache.formula'))) {
                BrewService::stop(config('env.apache.formula'));
            }
        });

        $this->installFormula(config('env.apache.formula'));

        $this->task('Configure Apache', function () {
            ApacheHelper::configure();
            File::ensureDirExists('/usr/local/var/log/httpd');
        });

        $this->task('Create default Virtual Host (localhost)', function ()  {
            ApacheHelper::initDefaultLocalhostVHost();
        });

        $this->call(StartCommand::COMMAND);
    }
}
