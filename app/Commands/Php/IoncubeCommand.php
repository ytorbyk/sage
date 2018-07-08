<?php

namespace App\Commands\Php;

use LaravelZero\Framework\Commands\Command;
use App\Commands\Apache\RestartCommand;

class IoncubeCommand extends Command
{
    const COMMAND = 'php:ioncube';

    /**
     * @var string
     */
    protected $signature = self::COMMAND
        . ' {action? : on|off}'
        . ' {--s|skip : Do not restart service}';

    /**
     * @var string
     */
    protected $description = 'Toggle IonCube PHP extension';

    /**
     * @var \App\Components\Site\IonCube
     */
    private $ionCube;

    /**
     * @var array
     */
    private $allowedActions = ['on', 'off'];

    /**
     * @param \App\Components\Site\IonCube $ionCube
     */
    public function __construct(
        \App\Components\Site\IonCube $ionCube
    ) {
        $this->ionCube = $ionCube;
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
        if (!$this->ionCube->isInstalled()) {
            $this->warn('IonCube is not installed');
            return false;
        }

        if ($this->ionCube->isEnabled()) {
            $this->warn('IonCube is already enabled');
            return false;
        }

        $this->job('IonCube enable', function () {
            $this->ionCube->enable();
        });
        return true;
    }

    /**
     * @return bool
     */
    private function disable(): bool
    {
        if (!$this->ionCube->isEnabled()) {
            $this->warn('IonCube is already disabled');
            return false;
        }

        $this->job('IonCube disable', function () {
            $this->ionCube->disable();
        });
        return true;
    }

    /**
     * @return null|string
     */
    private function getAction(): ?string
    {
        if ($this->ionCube->isEnabled()) {
            $action = 'off';
            $confirm = 'IonCube is enabled, do you want to disable?';
        } else {
            $action = 'on';
            $confirm = 'IonCube is disabled, do you want to enable?';
        }

        return $this->confirm($confirm, true) ? $action : null;
    }
}
