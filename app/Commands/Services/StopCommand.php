<?php

namespace App\Commands\Services;

use App\Command;

class StopCommand extends Command
{
    const COMMAND = 'services:stop';

    /**
     * @var string
     */
    protected $signature = self::COMMAND;

    /**
     * @var string
     */
    protected $description = 'Stop all services';

    /**
     * @return void
     */
    public function handle(): void
    {
        $services = config('env.services');

        $this->info('Stop services:');
        foreach ($services as $service) {
            $this->call($service . ':stop');
        }
    }
}
