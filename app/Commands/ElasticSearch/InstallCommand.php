<?php

declare(strict_types = 1);

namespace App\Commands\ElasticSearch;

use App\Command;
use App\Facades\Cli;
use App\Facades\Brew;

class InstallCommand extends Command
{
    const COMMAND = 'elasticsearch:install';

    /**
     * @var string
     */
    protected $signature = self::COMMAND;

    /**
     * @var string
     */
    protected $description = 'Install ElasticSearch';

    /**
     * @return void
     */
    public function handle(): void
    {
        $this->info('Install ElasticSearch:');

        $this->ensureJavaInstalled();

        Brew::tap('elastic/tap');
        $needInstall = $this->installFormula((string)config('env.elasticsearch.formula'));
        Brew::link((string)config('env.elasticsearch.formula'));

        $this->info('Install plugins:');
        $installedPlugins = $this->getInstalledPlugins();
        foreach ((array)config('env.elasticsearch.plugins') as $pluginName) {
            $this->info(sprintf('Installing [%s] plugin', $pluginName));
            if (in_array($pluginName, $installedPlugins, true)) {
                $this->comment('Already installed. Skip');
                continue;
            }
            Cli::passthru(sprintf('elasticsearch-plugin install %s', $pluginName));
        }

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

    /**
     * @return array
     */
    private function getInstalledPlugins(): array
    {
        $plugins = Cli::run('elasticsearch-plugin list');
        $plugins = !empty($plugins) ? explode(PHP_EOL, $plugins) : [];
        return array_map('trim', $plugins);
    }
}
