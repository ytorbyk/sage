<?php

declare(strict_types = 1);

namespace App\Commands\Memcached;

use App\Command;
use App\Facades\BrewService;

class StopCommand extends Command
{
    const COMMAND = 'memcached:stop';

    /**
     * @var string
     */
    protected $signature = self::COMMAND;

    /**
     * @var string
     */
    protected $description = 'Stop Memcached service';

    /**
     * @return void
     */
    public function handle(): void
    {
        $this->task('Memcached Stop', function () {
            try {
                BrewService::stop((string)config('env.memcached.formula'));
            } catch (\Exception $e) {
                return $e->getMessage();
            }
        });
    }
}
