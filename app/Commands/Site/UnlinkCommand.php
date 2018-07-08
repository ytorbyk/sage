<?php

namespace App\Commands\Site;

use LaravelZero\Framework\Commands\Command;
use App\Commands\Apache\HostRevokeCommand;
use App\Commands\Apache\RestartCommand;

class UnlinkCommand extends Command
{
    const COMMAND = 'site:unlink';

    /**
     * @var string
     */
    protected $signature = self::COMMAND . ' {domain}';

    /**
     * @var string
     */
    protected $description = 'Revoke site';

    /**
     * @return void
     */
    public function handle(): void
    {
        $this->info('Unlink website:');

        $this->call(HostRevokeCommand::COMMAND, ['domain' => $this->argument('domain')]);
        $this->call(RestartCommand::COMMAND);
    }
}
