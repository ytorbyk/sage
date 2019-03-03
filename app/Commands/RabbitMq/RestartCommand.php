<?php

namespace App\Commands\RabbitMq;

use App\Command;

class RestartCommand extends Command
{
    const COMMAND = 'rabbitmq:restart';

    /**
     * @var string
     */
    protected $signature = self::COMMAND;

    /**
     * @var string
     */
    protected $description = 'Restart RabbitMq service';

    /**
     * @return void
     */
    public function handle(): void
    {
        $this->info('RabbitMq restart:');

        $this->call(StopCommand::COMMAND);
        $this->call(StartCommand::COMMAND);
    }
}
