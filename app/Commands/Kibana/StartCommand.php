<?php

declare(strict_types = 1);

namespace App\Commands\Kibana;

use App\Command;
use App\Facades\BrewService;

class StartCommand extends Command
{
    const COMMAND = 'kibana:start';

    /**
     * @var string
     */
    protected $signature = self::COMMAND;

    /**
     * @var string
     */
    protected $description = 'Start Kibana service';

    /**
     * @return void
     */
    public function handle(): void
    {
        $this->task('Kibana Start', function () {
            BrewService::start((string)config('env.kibana.formula'));
        });
    }
}
