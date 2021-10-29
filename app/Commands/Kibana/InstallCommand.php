<?php

declare(strict_types = 1);

namespace App\Commands\Kibana;

use App\Command;
use App\Facades\Brew;
use App\Facades\ApacheHelper;
use App\Facades\Cli;
use App\Facades\Secure;

class InstallCommand extends Command
{
    const COMMAND = 'kibana:install';

    /**
     * @var string
     */
    protected $signature = self::COMMAND;

    /**
     * @var string
     */
    protected $description = 'Install Kibana';

    /**
     * @return void
     */
    public function handle(): void
    {
        $this->info('Install Kibana:');

        $this->ensureJavaInstalled();

        $needInstall = $this->installFormula((string)config('env.kibana.formula'));
        Brew::link((string)config('env.kibana.formula'));

        Secure::generate((string)config('env.kibana.domain'));
        ApacheHelper::configureProxyVHost((string)config('env.kibana.domain'), '5601');

        if ($needInstall) {
            $this->call(StartCommand::COMMAND);
        } else {
            $this->call(RestartCommand::COMMAND);
        }
    }

    /**
     * @return void
     */
    private function ensureJavaInstalled(): void
    {
        $javaVersion = $this->task('Ensure Java VM is installed', function () {
            $javaVersion = Cli::run('java -version 2>&1 | head -n 1 | cut -d\'"\' -f2');
            $javaVersion = trim($javaVersion);
            return (!empty($javaVersion) && strpos($javaVersion, 'No Java') === false) ? $javaVersion . '. Skip' : false;
        });

        if (strpos((string)$javaVersion, '1.8') !== 0) {
            Brew::tap('homebrew/cask', 'homebrew/cask-versions');
            Cli::passthru('brew install --cask temurin8');
        }
    }
}
