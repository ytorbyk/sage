<?php

declare(strict_types = 1);

namespace App\Commands\Apache;

use App\Command;
use App\Commands\Secure\RevokeCommand;
use App\Facades\ApacheHelper;

class HostRevokeCommand extends Command
{
    const COMMAND = 'apache:host-revoke';

    /**
     * @var string
     */
    protected $signature = self::COMMAND
        . ' {domain}';

    /**
     * @var string
     */
    protected $description = 'Delete Apache Virtual Host';

    /**
     * @return void
     */
    public function handle(): void
    {
        $domain = $this->argument('domain');

        $this->call(RevokeCommand::COMMAND, ['domain' => $domain]);

        $this->task('Delete Apache Virtual Host', function () use ($domain) {
            ApacheHelper::deleteVHost($domain);
        });
    }
}
