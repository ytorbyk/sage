<?php

namespace App\Commands\Apache;

use LaravelZero\Framework\Commands\Command;

class RestartCommand extends Command
{
    const COMMAND = 'apache:restart';

    /**
     * @var string
     */
    protected $signature = self::COMMAND;

    /**
     * @var string
     */
    protected $description = 'Restart Apache service';

    /**
     * @return void
     */
    public function handle(): void
    {
        $this->info('Apache Restart:');
        $this->call(StopCommand::COMMAND);
        $this->call(StartCommand::COMMAND);
    }
}
