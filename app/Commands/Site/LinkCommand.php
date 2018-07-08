<?php

namespace App\Commands\Site;

use LaravelZero\Framework\Commands\Command;
use App\Commands\Apache\HostCreateCommand;
use App\Commands\Apache\RestartCommand;

class LinkCommand extends Command
{
    const COMMAND = 'site:link';

    /**
     * @var string
     */
    protected $signature = self::COMMAND . ' {domain} {aliases?*}
        {--p|path= : Document root path}
        {--not-secure : Do not create secure virtual host}';

    /**
     * @var string
     */
    protected $description = 'Register site';

    /**
     * @return void
     */
    public function handle(): void
    {
        $this->info('Link website:');

        $this->call(HostCreateCommand::COMMAND, [
            'domain' => $this->argument('domain'),
            'aliases' => $this->argument('aliases'),
            '--path' => $this->option('path'),
            '--not-secure' => $this->option('not-secure')
        ]);

        $this->call(RestartCommand::COMMAND);
    }
}
