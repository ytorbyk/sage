<?php

declare(strict_types = 1);

namespace App\Commands\Kibana;

use App\Command;

class RestartCommand extends Command
{
    const COMMAND = 'kibana:restart';

    /**
     * @var string
     */
    protected $signature = self::COMMAND;

    /**
     * @var string
     */
    protected $description = 'Restart Kibana service';

    /**
     * @return void
     */
    public function handle(): void
    {
        $this->info('Kibana restart:');

        $this->call(StopCommand::COMMAND);
        $this->call(StartCommand::COMMAND);
    }
}
