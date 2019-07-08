<?php

declare(strict_types = 1);

namespace App\Commands\Kibana;

use App\Command;
use App\Facades\Brew;
use App\Facades\ApacheHelper;
use App\Facades\Secure;

class InstallCommand extends Command
{
    const COMMAND = 'kibana:install';

    /**
     * @var string
     */
    protected $signature = self::COMMAND;

    /**
     * @var string
     */
    protected $description = 'Install Kibana';

    /**
     * @return void
     */
    public function handle(): void
    {
        $this->info('Install Kibana:');

        $needInstall = $this->installFormula((string)config('env.kibana.formula'));
        Brew::link((string)config('env.kibana.formula'));

        Secure::generate((string)config('env.kibana.domain'));
        ApacheHelper::configureProxyVHost((string)config('env.kibana.domain'), '5601');

        if ($needInstall) {
            $this->call(StartCommand::COMMAND);
        } else {
            $this->call(RestartCommand::COMMAND);
        }
    }
}
