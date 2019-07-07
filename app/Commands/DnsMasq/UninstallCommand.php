<?php

declare(strict_types = 1);

namespace App\Commands\DnsMasq;

use App\Command;
use App\Facades\Brew;
use App\Facades\File;
use App\Facades\Cli;

class UninstallCommand extends Command
{
    const COMMAND = 'dns:uninstall';

    /**
     * @var string
     */
    protected $signature = self::COMMAND;

    /**
     * @var string
     */
    protected $description = 'Uninstall DnsMasq';

    /**
     * @return void
     */
    public function handle(): void
    {
        $this->info('Uninstall DnsMasq:');

        if (Brew::isInstalled((string)config('env.dns.formula'))) {
            $this->call(StopCommand::COMMAND);
            $this->uninstallFormula((string)config('env.dns.formula'));
        }

        $this->task('Delete DnsMasq config', function () {
            $this->deleteConfig();
        });
    }

    /**
     * @return void
     */
    private function deleteConfig()
    {
        Cli::run(sprintf('sudo rm -rf %s', config('env.dns.resolver_path')));
        File::delete(config('env.dns.brew_config_path'));
        File::deleteDirectory((string)config('env.dns.brew_config_dir_path'));
    }
}
