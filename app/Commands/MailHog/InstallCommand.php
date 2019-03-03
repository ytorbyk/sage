<?php

namespace App\Commands\MailHog;

use App\Command;

class InstallCommand extends Command
{
    const COMMAND = 'mailhog:install';

    /**
     * @var string
     */
    protected $signature = self::COMMAND;

    /**
     * @var string
     */
    protected $description = 'Install MailHog';

    /**
     * @return void
     */
    public function handle(): void
    {
        $this->info('Install MailHog:');

        $needInstall = $this->installFormula(config('env.mailhog.formula'));

        if ($needInstall) {
            $this->call(StartCommand::COMMAND);
        } else {
            $this->call(RestartCommand::COMMAND);
        }
    }
}
