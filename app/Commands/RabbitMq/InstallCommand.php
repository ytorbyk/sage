<?php

declare(strict_types = 1);

namespace App\Commands\RabbitMq;

use App\Command;
use App\Facades\ApacheHelper;
use App\Facades\Secure;

class InstallCommand extends Command
{
    const COMMAND = 'rabbitmq:install';

    /**
     * @var string
     */
    protected $signature = self::COMMAND;

    /**
     * @var string
     */
    protected $description = 'Install RabbitMq';

    /**
     * @return void
     */
    public function handle(): void
    {
        $this->info('Install RabbitMq:');

        $needInstall = $this->installFormula((string)config('env.rabbitmq.formula'));

        Secure::generate((string)config('env.rabbitmq.domain'));
        ApacheHelper::configureProxyVHost((string)config('env.rabbitmq.domain'), '15672');

        if ($needInstall) {
            $this->call(StartCommand::COMMAND);
        } else {
            $this->call(RestartCommand::COMMAND);
        }
    }
}
