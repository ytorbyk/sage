<?php

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

        $this->info('Install Java, since ElasticSearch depends on it:');
        Cli::passthru('brew cask install java');

        $needInstall = $this->installFormula(config('env.elasticsearch.formula'));

        Brew::link(config('env.elasticsearch.formula'));

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
     * @return array
     */
    private function getInstalledPlugins(): array
    {
        $plugins = Cli::run('elasticsearch-plugin list');
        $plugins = !empty($plugins) ? explode(PHP_EOL, $plugins) : [];
        return array_map('trim', $plugins);
    }
}
