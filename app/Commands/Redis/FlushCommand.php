<?php

declare(strict_types = 1);

namespace App\Commands\Redis;

use App\Command;
use App\Facades\Cli;

class FlushCommand extends Command
{
    const COMMAND = 'redis:flush';

    /**
     * @var string
     */
    protected $signature = self::COMMAND
        . ' {db? : DB 0|1|2 or empty for all}';

    /**
     * @var string
     */
    protected $description = 'Flush Redis';

    /**
     * @return void
     */
    public function handle(): void
    {
        $db = $this->argument('db');

        if (empty($db) && $db !== '0') {
            Cli::passthru('redis-cli FLUSHALL');
        } else {
            Cli::passthru(sprintf('redis-cli -n %d FLUSHDB', (int)$db));
        }
    }
}
