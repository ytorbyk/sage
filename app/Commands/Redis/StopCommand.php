<?php

declare(strict_types = 1);

namespace App\Commands\Redis;

use App\Command;
use App\Facades\BrewService;

class StopCommand extends Command
{
    const COMMAND = 'redis:stop';

    /**
     * @var string
     */
    protected $signature = self::COMMAND;

    /**
     * @var string
     */
    protected $description = 'Stop Redis service';

    /**
     * @return void
     */
    public function handle(): void
    {
        $this->task('Redis Stop', function () {
            try {
                BrewService::stop((string)config('env.redis.formula'));
            } catch (\Exception $e) {
                return $e->getMessage();
            }
        });
    }
}
