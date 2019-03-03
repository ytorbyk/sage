<?php

namespace App\Commands\RabbitMq;

use App\Command;

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

        $needInstall = $this->installFormula(config('env.rabbitmq.formula'));

        if ($needInstall) {
            $this->call(StartCommand::COMMAND);
        } else {
            $this->call(RestartCommand::COMMAND);
        }
    }
}
