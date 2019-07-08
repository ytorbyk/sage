<?php

declare(strict_types = 1);

namespace App\Commands\Memcached;

use App\Command;
use App\Facades\Cli;

class FlushCommand extends Command
{
    const COMMAND = 'memcached:flush';

    /**
     * @var string
     */
    protected $signature = self::COMMAND;

    /**
     * @var string
     */
    protected $description = 'Flush Memcached';

    /**
     * @return void
     */
    public function handle(): void
    {
        Cli::passthru("echo 'flush_all' | nc localhost 11211");
    }
}
