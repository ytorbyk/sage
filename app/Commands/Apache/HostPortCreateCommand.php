<?php

declare(strict_types = 1);

namespace App\Commands\Apache;

use App\Command;
use App\Commands\Secure\GenerateCommand;
use App\Facades\Secure;
use App\Facades\ApacheHelper;

class HostPortCreateCommand extends Command
{
    const COMMAND = 'apache:host-proxy';

    /**
     * @var string
     */
    protected $signature = self::COMMAND
        . ' {port} {domain} {aliases?*}'
        . ' {--not-secure : Do not create secure virtual host}';

    /**
     * @var string
     */
    protected $description = 'Create Apache Virtual Host Proxy';

    /**
     * @return void
     */
    public function handle(): void
    {
        $domain = $this->argument('domain');
        $aliases = $this->argument('aliases');
        $secure = !$this->option('not-secure');

        if (Secure::canSecure($domain) && $secure) {
            $this->call(GenerateCommand::COMMAND, ['domain' => $domain, 'aliases' => $aliases]);
        }

        $this->task('Create Apache Virtual Host-Proxy', function () use ($secure) {
            ApacheHelper::configureProxyVHost(
                $this->argument('domain'),
                $this->argument('port'),
                $this->argument('aliases'),
                $secure
            );
        });
    }
}
