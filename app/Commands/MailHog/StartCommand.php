<?php

namespace App\Commands\MailHog;

use App\Command;
use App\Facades\BrewService;

class StartCommand extends Command
{
    const COMMAND = 'mailhog:start';

    /**
     * @var string
     */
    protected $signature = self::COMMAND;

    /**
     * @var string
     */
    protected $description = 'Start MailHog service';

    /**
     * @return void
     */
    public function handle(): void
    {
        $this->task('MailHog Start', function () {
            BrewService::start(config('env.mailhog.formula'));
        });
    }
}
