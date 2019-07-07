<?php

declare(strict_types = 1);

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
            try {
                BrewService::stop((string)config('env.mailhog.formula'));
            } catch (\Exception $e) {
                return $e->getMessage();
            }
        });
    }
}
