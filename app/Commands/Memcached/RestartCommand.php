<?php

declare(strict_types = 1);

namespace App\Commands\Memcached;

use App\Command;

class RestartCommand extends Command
{
    const COMMAND = 'memcached:restart';

    /**
     * @var string
     */
    protected $signature = self::COMMAND;

    /**
     * @var string
     */
    protected $description = 'Restart Memcached service';

    /**
     * @return void
     */
    public function handle(): void
    {
        $this->info('Memcached restart:');

        $this->call(StopCommand::COMMAND);
        $this->call(StartCommand::COMMAND);
    }
}
