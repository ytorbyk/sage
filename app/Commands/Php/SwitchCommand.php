<?php

namespace App\Commands\Php;

use LaravelZero\Framework\Commands\Command;
use App\Commands\Apache\RestartCommand;

class SwitchCommand extends Command
{
    const COMMAND = 'php:switch';

    /**
     * @var string
     */
    protected $signature = self::COMMAND
        . ' {version? : PHP version like 5.6, 7.0, 7.1, 7.2}'
        . ' {--s|skip : Do not restart service}';

    /**
     * @var string
     */
    protected $description = 'Switch php version';

    /**
     * @var \App\Components\Site\Php
     */
    private $php;

    /**
     * @var \App\Components\Brew
     */
    private $brew;

    /**
     * @var \App\Components\Site\Apache
     */
    private $apache;

    /**
     * @param \App\Components\Site\Php $php
     * @param \App\Components\Brew $brew
     * @param \App\Components\Site\Apache $apache
     */
    public function __construct(
        \App\Components\Site\Php $php,
        \App\Components\Brew $brew,
        \App\Components\Site\Apache $apache
    ) {
        $this->php = $php;
        $this->brew = $brew;
        $this->apache = $apache;
        parent::__construct();
    }

    /**
     * @return void
     */
    public function handle(): void
    {
        $phpVersion = $this->argument('version') ?: $this->getVersion();

        if (!$phpVersion) {
            return;
        }

        if (!$this->brew->isInstalled($this->php->getFormula($phpVersion))) {
            $this->warn("PHP {$phpVersion} is not installed.");
            return;
        }

        $currentVersion = $this->php->getLinkedPhp();
        $supportedVersions = config('env.php.versions');

        if (!in_array($phpVersion, $supportedVersions, true)) {
            $this->warn("PHP {$phpVersion} is not available. The following versions are supported: " . implode(' ', $supportedVersions));
        }

        if ($phpVersion === $currentVersion) {
            $this->info("{$phpVersion} version is current. Skipping...");
            return;
        }

        $this->info('Enable PHP v' . $phpVersion . ':');

        $this->job('Relink php', function () use ($phpVersion) {
            $this->php->switchTo($phpVersion);
        });

        $this->job('Update apache config', function () use ($phpVersion) {
            $this->apache->linkPhp($phpVersion);
        });

        if (!$this->option('skip')) {
            $this->call(RestartCommand::COMMAND);
        }
    }

    /**
     * @return string|false
     */
    private function getVersion()
    {
        $option = $this->menu('Supported versions', config('env.php.versions'))
            ->setForegroundColour('green')
            ->setBackgroundColour('black')
            ->setExitButtonText('Cancel')
            ->open();

        return $option !== null ? config('env.php.versions')[$option] : false;
    }
}
