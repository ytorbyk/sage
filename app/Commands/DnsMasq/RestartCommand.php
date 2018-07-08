<?php

namespace App\Commands\DnsMasq;

use LaravelZero\Framework\Commands\Command;

class RestartCommand extends Command
{
    const COMMAND = 'dns:restart';

    /**
     * @var string
     */
    protected $signature = self::COMMAND;

    /**
     * @var string
     */
    protected $description = 'Restart DnsMasq service';

    /**
     * @return void
     */
    public function handle(): void
    {
        $this->info('DnsMasq restart:');

        $this->call(StopCommand::COMMAND);
        $this->call(StartCommand::COMMAND);
    }
}
