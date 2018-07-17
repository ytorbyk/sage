<?php

namespace App\Commands\Apache;

use App\Command;
use App\Facades\BrewService;

class StartCommand extends Command
{
    const COMMAND = 'apache:start';

    /**
     * @var string
     */
    protected $signature = self::COMMAND;

    /**
     * @var string
     */
    protected $description = 'Start Apache service';

    /**
     * @return void
     */
    public function handle(): void
    {
        $this->task('Apache Start', function () {
            BrewService::start(config('env.apache.formula'), true);
        });
    }
}
