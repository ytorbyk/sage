<?php

namespace App\Commands\Apache;

use LaravelZero\Framework\Commands\Command;

class UninstallCommand extends Command
{
    const COMMAND = 'apache:uninstall';

    /**
     * @var string
     */
    protected $signature = self::COMMAND;

    /**
     * @var string
     */
    protected $description = 'Uninstall Apache and remove configuration';

    /**
     * @var \App\Components\Brew
     */
    private $brew;

    /**
     * @var \App\Components\CommandLine
     */
    private $cli;

    /**
     * @var \App\Components\Files
     */
    private $files;

    /**
     * @param \App\Components\Brew $brew
     * @param \App\Components\CommandLine $cli
     * @param \App\Components\Files $files
     */
    public function __construct(
        \App\Components\Brew $brew,
        \App\Components\CommandLine $cli,
        \App\Components\Files $files
    ) {
        $this->brew = $brew;
        $this->cli = $cli;
        $this->files = $files;
        parent::__construct();
    }

    /**
     * @return void
     */
    public function handle(): void
    {
        $this->info('Uninstall Apache:');

        $this->job('Ensure Apache is not running', function () {
            $this->cli->runQuietly('sudo apachectl stop');
            $this->cli->runQuietly('sudo launchctl unload -w /System/Library/LaunchDaemons/org.apache.httpd.plist');
            $this->cli->runQuietly(sprintf('sudo brew services stop ', config('env.apache.formula')));
        });

        $isInstalled = $this->job('Need to be uninstalled?', function () {
            return $this->brew->isInstalled(config('env.apache.formula')) ?: 'Uninstalled. Skip';
        });

        if ($isInstalled === true) {
            $this->job(sprintf('Uninstall %s Brew formula', config('env.apache.formula')), function () {
                $this->brew->uninstall(config('env.apache.formula'), ['--force']);
            });
        }

        $this->job('Delete Apache configuration', function () {
            $this->files->delete(config('env.apache.config'));
            //$this->files->deleteDirectory(config('env.apache.vhosts'));
            $this->files->deleteDirectory(config('env.apache.localhost_path'));
            $this->files->deleteDirectory(config('env.apache.brew_config_dir_path'));
        });
    }
}
