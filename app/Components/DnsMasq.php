<?php

namespace App\Components;

class DnsMasq
{
    /**
     * @var \App\Components\CommandLine
     */
    private $cli;

    /**
     * @var \App\Components\Files
     */
    private $files;

    /**
     * @param \App\Components\CommandLine $cli
     * @param \App\Components\Files $files
     */
    public function __construct(
        \App\Components\CommandLine $cli,
        \App\Components\Files $files
    ) {
        $this->cli = $cli;
        $this->files = $files;
    }

    /**
     * @return void
     */
    public function configure(): void
    {
        $this->createCustomConfigFile();
        $domains = (array)config('env.dns.domains');
        $this->setConfig($domains);
        $this->createDomainResolvers($domains);
    }

    /**
     * @return void
     */
    public function deleteConfig()
    {
        $this->cli->run(sprintf('sudo rm -rf %s', config('env.dns.resolver_path')));
        $this->files->delete(config('env.dns.brew_config_path'));
        $this->files->deleteDirectory(config('env.dns.brew_config_dir_path'));
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

        $this->files->put(config('env.dns.config_path'), $content);
    }

    /**
     * @param string[] $domains
     * @return void
     */
    private function createDomainResolvers($domains)
    {
        $this->cli->run(sprintf('sudo rm -rf %s', config('env.dns.resolver_path')));
        $this->cli->run(sprintf('sudo mkdir %s', config('env.dns.resolver_path')));
        foreach ($domains as $domain) {
            $this->cli->run(sprintf('sudo bash -c "echo \'nameserver 127.0.0.1\' > %s/%s"', config('env.dns.resolver_path'), $domain));
        }
    }

    /**
     * @return void
     */
    private function createCustomConfigFile()
    {
        $this->appendCustomConfigImport(config('env.dns.config_path'));
    }

    /**
     * Append import command for our custom configuration to DnsMasq file.
     *
     * @param  string  $customConfigPath
     * @return void
     */
    private function appendCustomConfigImport($customConfigPath)
    {
        if (!$this->customConfigIsImported($customConfigPath)) {
            $this->files->put(
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
        return strpos($this->files->get(config('env.dns.brew_config_path')), $customConfigPath) !== false;
    }
}
