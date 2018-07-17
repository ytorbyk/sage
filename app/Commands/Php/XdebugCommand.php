<?php

namespace App\Commands\Php;

use App\Command;
use App\Commands\Apache\RestartCommand;
use App\Services\Pecl;
use App\Facades\PeclHelper;
use App\Facades\File;

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
     * @var array
     */
    private $allowedActions = ['on', 'off'];

    /**
     * @return void
     */
    public function handle(): void
    {
        $action = $this->argument('action');

        if (!$action) {
            $action = $this->getAction();
        } elseif (!in_array($action, $this->allowedActions, true)) {
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
        if (!PeclHelper::isInstalled(Pecl::XDEBUG_EXTENSION)) {
            $this->warn('xDebug is not installed');
            return false;
        }

        if (PeclHelper::isEnabled(Pecl::XDEBUG_EXTENSION)) {
            $this->warn('xDebug is already enabled');
            return false;
        }

        $this->task('xDebug enable', function () {
            PeclHelper::enable(Pecl::XDEBUG_EXTENSION);
        });

        if ($remoteAutostart) {
            $this->task('xDebug enable autostart', function () {
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
        $configContent = File::get(PeclHelper::iniPath(Pecl::XDEBUG_EXTENSION));
        $configContent = str_replace('xdebug.remote_autostart=0', 'xdebug.remote_autostart=1', $configContent);
        File::put(PeclHelper::iniPath(Pecl::XDEBUG_EXTENSION), $configContent);
    }

    /**
     * @return bool
     */
    private function disable(): bool
    {
        if (!PeclHelper::isEnabled(Pecl::XDEBUG_EXTENSION)) {
            $this->warn('xDebug is already disabled');
            return false;
        }

        $this->task('xDebug disable', function () {
            $this->disableAutostart();
            PeclHelper::disable(Pecl::XDEBUG_EXTENSION);
        });
        return true;
    }

    /**
     * @return void
     */
    private function disableAutostart(): void
    {
        $configContent = File::get(PeclHelper::iniPath(Pecl::XDEBUG_EXTENSION));
        $configContent = str_replace('xdebug.remote_autostart=1', 'xdebug.remote_autostart=0', $configContent);
        File::put(PeclHelper::iniPath(Pecl::XDEBUG_EXTENSION), $configContent);
    }

    /**
     * @return null|string
     */
    private function getAction(): ?string
    {
        if (PeclHelper::isEnabled(Pecl::XDEBUG_EXTENSION)) {
            $action = 'off';
            $confirm = 'xDebug is enabled, do you want to disable?';
        } else {
            $action = 'on';
            $confirm = 'xDebug is disabled, do you want to enable?';
        }

        return $this->confirm($confirm, true) ? $action : null;
    }
}
