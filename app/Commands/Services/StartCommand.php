<?php

namespace App\Commands\Services;

use App\Command;

class StartCommand extends Command
{
    const COMMAND = 'services:start';

    /**
     * @var string
     */
    protected $signature = self::COMMAND;

    /**
     * @var string
     */
    protected $description = 'Start all services';

    /**
     * @return void
     */
    public function handle(): void
    {
        $services = config('env.services');

        $this->info('Start services:');
        foreach ($services as $service) {
            $this->call($service . ':start');
        }
    }
}
