<?php

declare(strict_types = 1);

namespace App\Commands\Redis;

use App\Command;

class RestartCommand extends Command
{
    const COMMAND = 'redis:restart';

    /**
     * @var string
     */
    protected $signature = self::COMMAND;

    /**
     * @var string
     */
    protected $description = 'Restart Redis service';

    /**
     * @return void
     */
    public function handle(): void
    {
        $this->info('Redis restart:');

        $this->call(StopCommand::COMMAND);
        $this->call(StartCommand::COMMAND);
    }
}
