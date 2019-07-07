<?php

declare(strict_types = 1);

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
            try {
                BrewService::stop((string)config('env.rabbitmq.formula'));
            } catch (\Exception $e) {
                return $e->getMessage();
            }
        });
    }
}
