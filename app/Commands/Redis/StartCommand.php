<?php

declare(strict_types = 1);

namespace App\Commands\Redis;

use App\Command;
use App\Facades\BrewService;

class StartCommand extends Command
{
    const COMMAND = 'redis:start';

    /**
     * @var string
     */
    protected $signature = self::COMMAND;

    /**
     * @var string
     */
    protected $description = 'Start Redis service';

    /**
     * @return void
     */
    public function handle(): void
    {
        $this->task('Redis Start', function () {
            try {
                BrewService::start((string)config('env.redis.formula'));
            } catch (\Exception $e) {
                return $e->getMessage();
            }
        });
    }
}
