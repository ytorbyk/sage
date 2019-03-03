<?php

namespace App\Commands\RabbitMq;

use App\Command;
use App\Facades\BrewService;

class StopCommand extends Command
{
    const COMMAND = 'rabbitmq:stop';

    /**
     * @var string
     */
    protected $signature = self::COMMAND;

    /**
     * @var string
     */
    protected $description = 'Stop RabbitMq service';

    /**
     * @return void
     */
    public function handle(): void
    {
        $this->task('RabbitMq Stop', function () {
            BrewService::stop(config('env.rabbitmq.formula'));
        });
    }
}
