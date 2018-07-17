<?php

namespace App\Commands\Php;

use App\Command;
use App\Commands\Apache\RestartCommand;
use App\Facades\Brew;
use App\Facades\ApacheHelper;
use App\Facades\PhpHelper;

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
     * @return void
     */
    public function handle(): void
    {
        $phpVersion = $this->argument('version') ?: $this->getVersion();

        if (!$phpVersion) {
            return;
        }

        if (!Brew::isInstalled(PhpHelper::getFormula($phpVersion))) {
            $this->warn("PHP {$phpVersion} is not installed.");
            return;
        }

        $currentVersion = PhpHelper::getLinkedPhp();
        $supportedVersions = config('env.php.versions');

        if (!in_array($phpVersion, $supportedVersions, true)) {
            $this->warn("PHP {$phpVersion} is not available. The following versions are supported: " . implode(' ', $supportedVersions));
        }

        if ($phpVersion === $currentVersion) {
            $this->info("{$phpVersion} version is current. Skipping...");
            return;
        }

        $this->info('Enable PHP v' . $phpVersion . ':');

        $this->task('Relink php', function () use ($phpVersion) {
            PhpHelper::switchTo($phpVersion);
        });

        $this->task('Update apache config', function () use ($phpVersion) {
            ApacheHelper::linkPhp($phpVersion);
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
        $option = $this->menu('Switch PHP', config('env.php.versions'))
            ->setForegroundColour('green')
            ->setBackgroundColour('black')
            ->setExitButtonText('Cancel')
            ->addLineBreak('-')
            ->open();

        return $option !== null ? config('env.php.versions')[$option] : false;
    }
}
