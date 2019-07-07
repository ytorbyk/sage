<?php

declare(strict_types = 1);

namespace App\Commands\Apache;

use App\Command;

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
