<?php

declare(strict_types = 1);

namespace App\Commands\Apache;

use App\Command;
use App\Facades\Brew;
use App\Facades\BrewService;
use App\Facades\Cli;
use App\Facades\File;

class UninstallCommand extends Command
{
    const COMMAND = 'apache:uninstall';

    /**
     * @var string
     */
    protected $signature = self::COMMAND
        . ' {--f|force : Delete virtual host\'s configs}';

    /**
     * @var string
     */
    protected $description = 'Uninstall Apache and remove configuration';

    /**
     * @return void
     */
    public function handle(): void
    {
        $this->info('Uninstall Apache:');

        $this->task('Ensure Apache is not running', function () {
            Cli::runQuietly('sudo apachectl stop');
            Cli::runQuietly('sudo launchctl unload -w /System/Library/LaunchDaemons/org.apache.httpd.plist');

            if (Brew::isInstalled((string)config('env.apache.formula'))) {
                try {
                    BrewService::stop((string)config('env.apache.formula'));
                } catch (\Exception $e) {
                    return $e->getMessage();
                }
            }
        });

        $this->uninstallFormula((string)config('env.apache.formula'));

        $this->task('Delete Apache configuration', function () {
            File::delete((string)config('env.apache.config'));
            File::deleteDirectory((string)config('env.apache.localhost_path'));
            File::deleteDirectory((string)config('env.apache.brew_config_dir_path'));
        });

        if ($this->option('force')) {
            File::deleteDirectory((string)config('env.apache.vhosts'));
        }
    }
}
