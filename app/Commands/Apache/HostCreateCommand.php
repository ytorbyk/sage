<?php

declare(strict_types = 1);

namespace App\Commands\Apache;

use App\Command;
use App\Commands\Secure\GenerateCommand;
use App\Facades\Secure;
use App\Facades\ApacheHelper;

class HostCreateCommand extends Command
{
    const COMMAND = 'apache:host';

    /**
     * @var string
     */
    protected $signature = self::COMMAND
        . ' {domain} {aliases?*}'
        . ' {--p|path= : Document root path}'
        . ' {--not-secure : Do not create secure virtual host}';

    /**
     * @var string
     */
    protected $description = 'Create Apache Virtual Host';

    /**
     * @return void
     */
    public function handle(): void
    {
        $domain = $this->argument('domain');
        $aliases = $this->argument('aliases');
        $secure = !$this->option('not-secure');
        $path = $this->option('path');

        $hostPath = $this->getCurrentPath($path);
        if (!$this->verifyPath($hostPath, false)) {
            $this->error('Passed path does not exist or not a folder: ' . $hostPath);
            return;
        }

        if (Secure::canSecure($domain) && $secure) {
            $this->call(GenerateCommand::COMMAND, ['domain' => $domain, 'aliases' => $aliases]);
        }

        $this->task('Create Apache Virtual Host', function () use ($hostPath, $secure) {
            ApacheHelper::configureVHost(
                $this->argument('domain'),
                $hostPath,
                $this->argument('aliases'),
                $secure
            );
        });
    }
}
