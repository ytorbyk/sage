<?php

namespace App\Commands\Apache;

use LaravelZero\Framework\Commands\Command;

class InstallCommand extends Command
{
    const COMMAND = 'apache:install';

    /**
     * @var string
     */
    protected $signature = self::COMMAND;

    /**
     * @var string
     */
    protected $description = 'Install and configure Apache';

    /**
     * @var \App\Components\Brew
     */
    private $brew;

    /**
     * @var \App\Components\Site\Apache
     */
    private $apache;

    /**
     * @var \App\Components\CommandLine
     */
    private $cli;

    /**
     * @param \App\Components\Brew $brew
     * @param \App\Components\Site\Apache $apache
     * @param \App\Components\CommandLine $cli
     */
    public function __construct(
        \App\Components\Brew $brew,
        \App\Components\Site\Apache $apache,
        \App\Components\CommandLine $cli
    ) {
        $this->brew = $brew;
        $this->apache = $apache;
        $this->cli = $cli;
        parent::__construct();
    }

    /**
     * @return void
     */
    public function handle(): void
    {
        $this->info('Install Apache:');

        $this->job('Ensure Apache is not running', function () {
            $this->cli->runQuietly('sudo apachectl stop');
            $this->cli->runQuietly('sudo launchctl unload -w /System/Library/LaunchDaemons/org.apache.httpd.plist');
            $this->cli->runQuietly(sprintf('sudo brew services stop ', config('env.apache.formula')));
        });

        $needInstall = $this->job('Need to be installed?', function () {
            return !$this->brew->isInstalled(config('env.apache.formula')) ?: 'Installed. Skip';
        });

        if ($needInstall === true) {
            $this->job(sprintf('Install [%s] Brew formula', config('env.apache.formula')), function () {
                $this->brew->install(config('env.apache.formula'));
            });
        }

        $this->job('Configure Apache', function () {
            $this->apache->configure();
        });

        $this->job('Create default Virtual Host (localhost)', function () {
            $this->apache->initDefaultLocalhostVHost();
        });

        $this->call(StartCommand::COMMAND);
    }
}
