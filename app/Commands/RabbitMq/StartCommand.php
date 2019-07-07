<?php

declare(strict_types = 1);

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
            try {
                BrewService::start((string)config('env.rabbitmq.formula'));
            } catch (\Exception $e) {
                return $e->getMessage();
            }
        });
    }
}
