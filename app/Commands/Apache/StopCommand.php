<?php

namespace App\Commands\Apache;

use App\Command;
use App\Facades\BrewService;

class StopCommand extends Command
{
    const COMMAND = 'apache:stop';

    /**
     * @var string
     */
    protected $signature = self::COMMAND;

    /**
     * @var string
     */
    protected $description = 'Stop Apache service';

    /**
     * @return void
     */
    public function handle(): void
    {
        $this->task('Apache Stop', function () {
            BrewService::stop(config('env.apache.formula'));
        });
    }
}
