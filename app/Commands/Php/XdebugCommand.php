<?php

namespace App\Commands\Php;

use LaravelZero\Framework\Commands\Command;
use App\Commands\Apache\RestartCommand;
use App\Components\Site\Pecl;

class XdebugCommand extends Command
{
    const COMMAND = 'php:xdebug';

    /**
     * @var string
     */
    protected $signature = self::COMMAND
        . ' {action? : on|off}'
        . ' {--s|skip : Do not restart service}'
        . ' {--a|remote-autostart : Enable remote autostart on enabling}';

    /**
     * @var string
     */
    protected $description = 'Toggle xDebug PHP extension';

    /**
     * @var \App\Components\Site\Pecl
     */
    private $pecl;

    /**
     * @var \App\Components\Files
     */
    private $files;

    /**
     * @var array
     */
    private $allowedActions = ['on', 'off'];

    /**
     * @param \App\Components\Site\Pecl $pecl
     * @param \App\Components\Files $files
     */
    public function __construct(
        \App\Components\Site\Pecl $pecl,
        \App\Components\Files $files
    ) {
        $this->pecl = $pecl;
        $this->files = $files;
        parent::__construct();
    }

    /**
     * @return void
     */
    public function handle(): void
    {
        $action = $this->argument('action');

        if (!$action) {
            $action = $this->getAction();
        } elseif (!in_array($action, $this->allowedActions)) {
            $this->warn('Wrong action. Allowed actions: ' . implode('|', $this->allowedActions));
            return;
        }

        $shouldApacheRestart = false;

        if ($action === 'on') {
            $shouldApacheRestart = $this->enable($this->option('remote-autostart'));
        }

        if ($action === 'off') {
            $shouldApacheRestart = $this->disable();
        }

        if (!$this->option('skip') && $shouldApacheRestart) {
            $this->call(RestartCommand::COMMAND);
        }
    }

    /**
     * @param bool $remoteAutostart
     * @return bool
     */
    private function enable(bool $remoteAutostart = false): bool
    {
        if (!$this->pecl->isInstalled(Pecl::XDEBUG_EXTENSION)) {
            $this->warn('xDebug is not installed');
            return false;
        }

        if ($this->pecl->isEnabled(Pecl::XDEBUG_EXTENSION)) {
            $this->warn('xDebug is already enabled');
            return false;
        }

        $this->job('xDebug enable', function () {
            $this->pecl->enable(Pecl::XDEBUG_EXTENSION);
        });

        if ($remoteAutostart) {
            $this->job('xDebug enable autostart', function () {
                $this->enableAutostart();
            });
        }
        return true;
    }

    /**
     * @return void
     */
    private function enableAutostart(): void
    {
        $configContent = $this->files->get($this->pecl->iniPath(Pecl::XDEBUG_EXTENSION));
        $configContent = str_replace('xdebug.remote_autostart=0', 'xdebug.remote_autostart=1', $configContent);
        $this->files->put($this->pecl->iniPath(Pecl::XDEBUG_EXTENSION), $configContent);
    }

    /**
     * @return bool
     */
    private function disable(): bool
    {
        if (!$this->pecl->isEnabled(Pecl::XDEBUG_EXTENSION)) {
            $this->warn('xDebug is already disabled');
            return false;
        }

        $this->job('xDebug disable', function () {
            $this->disableAutostart();
            $this->pecl->disable(Pecl::XDEBUG_EXTENSION);
        });
        return true;
    }

    /**
     * @return void
     */
    private function disableAutostart(): void
    {
        $configContent = $this->files->get($this->pecl->iniPath(Pecl::XDEBUG_EXTENSION));
        $configContent = str_replace('xdebug.remote_autostart=1', 'xdebug.remote_autostart=0', $configContent);
        $this->files->put($this->pecl->iniPath(Pecl::XDEBUG_EXTENSION), $configContent);
    }

    /**
     * @return null|string
     */
    private function getAction(): ?string
    {
        if ($this->pecl->isEnabled(Pecl::XDEBUG_EXTENSION)) {
            $action = 'off';
            $confirm = 'xDebug is enabled, do you want to disable?';
        } else {
            $action = 'on';
            $confirm = 'xDebug is disabled, do you want to enable?';
        }

        return $this->confirm($confirm, true) ? $action : null;
    }
}
