<?php

declare(strict_types = 1);

namespace App\Commands\Site;

use App\Command;
use App\Commands\Apache\HostPortCreateCommand;
use App\Commands\Apache\RestartCommand;

class LinkProxyCommand extends Command
{
    const COMMAND = 'site:proxy:link';

    /**
     * @var string
     */
    protected $signature = self::COMMAND
        . ' {port} {domain} {aliases?*}'
        . ' {--not-secure : Do not create secure virtual host}';

    /**
     * @var string
     */
    protected $description = 'Register proxy site';

    /**
     * @return void
     */
    public function handle(): void
    {
        $this->info('Link proxy website:');

        $this->call(HostPortCreateCommand::COMMAND, [
            'port' => $this->argument('port'),
            'domain' => $this->argument('domain'),
            'aliases' => $this->argument('aliases'),
            '--not-secure' => $this->option('not-secure')
        ]);

        $this->call(RestartCommand::COMMAND);
    }
}
