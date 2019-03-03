<?php

namespace App\Commands\MailHog;

use App\Command;
use App\Facades\BrewService;

class StopCommand extends Command
{
    const COMMAND = 'mailhog:stop';

    /**
     * @var string
     */
    protected $signature = self::COMMAND;

    /**
     * @var string
     */
    protected $description = 'Stop MailHog service';

    /**
     * @return void
     */
    public function handle(): void
    {
        $this->task('MailHog Stop', function () {
            BrewService::stop(config('env.mailhog.formula'));
        });
    }
}
