<?php

declare(strict_types = 1);

namespace App\Commands\MySql;

use App\Command;

class RestartCommand extends Command
{
    const COMMAND = 'mysql:restart';

    /**
     * @var string
     */
    protected $signature = self::COMMAND;

    /**
     * @var string
     */
    protected $description = 'Restart MySQL service';

    /**
     * @return void
     */
    public function handle(): void
    {
        $this->info('MySQL Restart:');

        $this->call(StopCommand::COMMAND);
        $this->call(StartCommand::COMMAND);
    }
}
