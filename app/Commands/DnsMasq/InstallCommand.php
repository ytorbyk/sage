<?php

namespace App\Commands\DnsMasq;

use App\Command;
use App\Facades\File;
use App\Facades\Cli;

class InstallCommand extends Command
{
    const COMMAND = 'dns:install';

    /**
     * @var string
     */
    protected $signature = self::COMMAND;

    /**
     * @var string
     */
    protected $description = 'Install and configure DnsMasq';

    /**
     * @return void
     */
    public function handle(): void
    {
        $this->info('Install DnsMasq:');

        $needInstall = $this->installFormula(config('env.dns.formula'));

        $this->task('Configure DnsMasq', function () {
            $this->configureDnsMasq();
        });

        if ($needInstall) {
            $this->call(StartCommand::COMMAND);
        } else {
            $this->call(RestartCommand::COMMAND);
        }
    }

    /**
     * @return void
     */
    private function configureDnsMasq(): void
    {
        $this->createCustomConfigFile();
        $domains = (array)config('env.dns.domains');
        $this->setConfig($domains);
        $this->createDomainResolvers($domains);
    }

    /**
     * @return void
     */
    private function createCustomConfigFile()
    {
        $customConfigPath = config('env.dns.config_path');
        if (!$this->customConfigIsImported($customConfigPath)) {
            File::put(
                config('env.dns.brew_config_path'),
                'conf-file=' . $customConfigPath . PHP_EOL
            );
        }
    }

    /**
     * @param string $customConfigPath
     * @return bool
     */
    private function customConfigIsImported($customConfigPath)
    {
        return strpos(File::get(config('env.dns.brew_config_path')), $customConfigPath) !== false;
    }

    /**
     * @param string[] $domains
     * @return void
     */
    private function setConfig($domains)
    {
        $content = '';
        foreach ($domains as $domain) {
            $content .= 'address=/.' . $domain . '/127.0.0.1' . PHP_EOL;
        }

        File::put(config('env.dns.config_path'), $content);
    }

    /**
     * @param string[] $domains
     * @return void
     */
    private function createDomainResolvers($domains)
    {
        Cli::run(sprintf('sudo rm -rf %s', config('env.dns.resolver_path')));
        Cli::run(sprintf('sudo mkdir %s', config('env.dns.resolver_path')));

        foreach ($domains as $domain) {
            Cli::run(sprintf('sudo bash -c "echo \'nameserver 127.0.0.1\' > %s/%s"', config('env.dns.resolver_path'), $domain));
        }
    }
}
