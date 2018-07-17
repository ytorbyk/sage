<?php

namespace App\Commands\DnsMasq;

use App\Command;
use App\Facades\BrewService;

class StopCommand extends Command
{
    const COMMAND = 'dns:stop';

    /**
     * @var string
     */
    protected $signature = self::COMMAND;

    /**
     * @var string
     */
    protected $description = 'Stop DnsMasq service';

    /**
     * @return void
     */
    public function handle(): void
    {
        $this->task('DnsMasq Start', function () {
            BrewService::stop(config('env.dns.formula'));
        });
    }
}
