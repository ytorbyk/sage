<?php

declare(strict_types = 1);

namespace App\Commands\Php;

use App\Command;
use App\Commands\Apache\RestartCommand;
use App\Facades\MemcachedSession;

class MemcachedSessionCommand extends Command
{
    const COMMAND = 'php:session:memcached';

    /**
     * @var string
     */
    protected $signature = self::COMMAND
        . ' {action? : on|off}'
        . ' {--s|skip : Do not restart service}';

    /**
     * @var string
     */
    protected $description = 'Toggle using memcached as session storage';

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
            $shouldApacheRestart = $this->enable();
        }

        if ($action === 'off') {
            $shouldApacheRestart = $this->disable();
        }


        if (!$this->option('skip') && $shouldApacheRestart) {
            $this->call(RestartCommand::COMMAND);
        }
    }

    /**
     * @return bool
     */
    private function enable(): bool
    {
        if (!MemcachedSession::isInstalled()) {
            $this->warn('Memcached is not installed');
            return false;
        }

        if (MemcachedSession::isEnabled()) {
            $this->warn('Memcached as session storage is already enabled');
            return false;
        }

        $this->task('Memcached as session storage enable', function () {
            MemcachedSession::enable();
        });
        return true;
    }

    /**
     * @return bool
     */
    private function disable(): bool
    {
        if (!MemcachedSession::isEnabled()) {
            $this->warn('Memcached as session storage is already disabled');
            return false;
        }

        $this->task('Memcached as session storage disable', function () {
            MemcachedSession::disable();
        });
        return true;
    }

    /**
     * @return null|string
     */
    private function getAction(): ?string
    {
        if (MemcachedSession::isEnabled()) {
            $action = 'off';
            $confirm = 'Memcached as session storage is enabled, do you want to disable?';
        } else {
            $action = 'on';
            $confirm = 'Memcached as session storage is disabled, do you want to enable?';
        }

        return $this->confirm($confirm, true) ? $action : null;
    }
}
