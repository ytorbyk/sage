<?php

namespace App\Commands\MailHog;

use App\Command;

class RestartCommand extends Command
{
    const COMMAND = 'mailhog:restart';

    /**
     * @var string
     */
    protected $signature = self::COMMAND;

    /**
     * @var string
     */
    protected $description = 'Restart MailHog service';

    /**
     * @return void
     */
    public function handle(): void
    {
        $this->info('MailHog restart:');

        $this->call(StopCommand::COMMAND);
        $this->call(StartCommand::COMMAND);
    }
}
