<?php

namespace App\Commands\RabbitMq;

use App\Command;
use App\Facades\BrewService;

class StartCommand extends Command
{
    const COMMAND = 'rabbitmq:start';

    /**
     * @var string
     */
    protected $signature = self::COMMAND;

    /**
     * @var string
     */
    protected $description = 'Start RabbitMq service';

    /**
     * @return void
     */
    public function handle(): void
    {
        $this->task('RabbitMq Start', function () {
            BrewService::start(config('env.rabbitmq.formula'));
        });
    }
}
