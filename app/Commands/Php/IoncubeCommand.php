<?php

declare(strict_types = 1);

namespace App\Commands\Php;

use App\Command;
use App\Commands\Apache\RestartCommand;
use App\Facades\IonCubeHelper;

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
        if (!IonCubeHelper::isInstalled()) {
            $this->warn('IonCube is not installed');
            return false;
        }

        if (IonCubeHelper::isEnabled()) {
            $this->warn('IonCube is already enabled');
            return false;
        }

        $this->task('IonCube enable', function () {
            IonCubeHelper::enable();
        });
        return true;
    }

    /**
     * @return bool
     */
    private function disable(): bool
    {
        if (!IonCubeHelper::isEnabled()) {
            $this->warn('IonCube is already disabled');
            return false;
        }

        $this->task('IonCube disable', function () {
            IonCubeHelper::disable();
        });
        return true;
    }

    /**
     * @return null|string
     */
    private function getAction(): ?string
    {
        if (IonCubeHelper::isEnabled()) {
            $action = 'off';
            $confirm = 'IonCube is enabled, do you want to disable?';
        } else {
            $action = 'on';
            $confirm = 'IonCube is disabled, do you want to enable?';
        }

        return $this->confirm($confirm, true) ? $action : null;
    }
}
