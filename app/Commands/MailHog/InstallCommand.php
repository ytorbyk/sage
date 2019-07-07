<?php

declare(strict_types = 1);

namespace App\Commands\MailHog;

use App\Command;
use App\Facades\ApacheHelper;

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

        $needInstall = $this->installFormula((string)config('env.mailhog.formula'));
        ApacheHelper::configureProxyVHost((string)config('env.mailhog.domain'), '8025');

        if ($needInstall) {
            $this->call(StartCommand::COMMAND);
        } else {
            $this->call(RestartCommand::COMMAND);
        }
    }
}
