<?php

namespace App\Components\Site;

class Apache
{
    /**
     * @var \App\Components\Brew\Service
     */
    private $brewService;

    /**
     * @var \App\Components\Site\Secure
     */
    private $secure;

    /**
     * @var \App\Components\Files
     */
    private $files;

    /**
     * @var \App\Components\Stubs
     */
    private $stubs;

    /**
     * @param \App\Components\Brew\Service $brewService
     * @param \App\Components\Site\Secure $secure
     * @param \App\Components\Files $files
     * @param \App\Components\Stubs $stubs
     */
    public function __construct(
        \App\Components\Brew\Service $brewService,
        \App\Components\Site\Secure $secure,
        \App\Components\Files $files,
        \App\Components\Stubs $stubs
    ) {
        $this->brewService = $brewService;
        $this->secure = $secure;
        $this->files = $files;
        $this->stubs = $stubs;
    }

    /**
     * @return void
     */
    public function start(): void
    {
        $this->brewService->start(config('env.apache.formula'), true);
    }

    /**
     * @return void
     */
    public function stop(): void
    {
        $this->brewService->stop(config('env.apache.formula'));
    }

    /**
     * @return void
     */
    public function restart(): void
    {
        $this->brewService->restart(config('env.apache.formula'), true);
    }

    /**
     * @param string $domain
     * @return void
     */
    public function deleteVHost(string $domain): void
    {
        $this->files->delete($this->getConfPath($domain));
    }

    /**
     * @param string $documentRoot
     * @param string $domain
     * @param string[] $aliases
     * @param bool $secure
     * @return void
     */
    public function configureVHost(string $documentRoot, string $domain, array $aliases = [], bool $secure = true)
    {
        $this->files->ensureDirExists(config('env.apache.vhosts'));
        $this->deleteVHost($domain);

        $serverAliases = !empty($aliases)
            ? 'ServerAlias ' . implode(' ', $aliases)
            : '';

        $virtualHostSsl = '';
        if ($secure && $this->secure->canSecure($domain)) {
            $virtualHostSsl = $this->stubs->get(
                'httpd-vhost-ssl.conf',
                [
                    'DOMAIN' => $domain,
                    'SERVER_ALIAS' => $serverAliases,
                    'DOCUMENT_ROOT' => $documentRoot,
                    'LOGS_PATH' => config('env.logs_path'),
                    'CERTIFICATE_CRT' => $this->secure->getFilePath($domain, 'crt'),
                    'CERTIFICATE_KEY' => $this->secure->getFilePath($domain, 'key')
                ]
            );
        }

        $vhostConfig = $this->stubs->get(
            'httpd-vhost.conf',
            [
                'DOMAIN' => $domain,
                'SERVER_ALIAS' => $serverAliases,
                'DOCUMENT_ROOT' => $documentRoot,
                'LOGS_PATH' => config('env.logs_path'),
                'VIRTUAL_HOST_SSL' => $virtualHostSsl,
            ]
        );
        $this->files->put($this->getConfPath($domain), $vhostConfig);
    }

    /**
     * @param string $version
     * @return void
     */
    public function linkPhp(string $version): void
    {
        $config = $this->files->get(config('env.apache.config'));
        foreach (config('env.php.versions') as $phpVersion) {
            $config = $this->removePhpVersion($config, $phpVersion);
        }

        $phpModuleHeader = config('env.apache.php_module_header') . PHP_EOL;
        if (strpos($config, $phpModuleHeader) === false) {
            throw new \RuntimeException('Apache config is broken');
        }

        $phpModuleLoad = $this->getPhpModuleLoad($version);
        $config = str_replace($phpModuleHeader, $phpModuleHeader . $phpModuleLoad . PHP_EOL, $config);
        $this->files->put(config('env.apache.config'), $config);
    }

    /**
     * @param string $config
     * @param string $phpVersion
     * @return string
     */
    private function removePhpVersion(string $config, string $phpVersion): string
    {
        $phpModuleLoad = $this->getPhpModuleLoad($phpVersion);

        $config = str_replace('#' . $phpModuleLoad . PHP_EOL, '', $config);
        $config = str_replace('#' . $phpModuleLoad, '', $config);
        $config = str_replace($phpModuleLoad . PHP_EOL, '', $config);
        $config = str_replace($phpModuleLoad, '', $config);

        return $config;
    }

    /**
     * @param string $phpVersion
     * @return string
     */
    private function getPhpModuleLoad($phpVersion): string
    {
        $highVersion = explode('.', $phpVersion);
        $highVersion = array_shift($highVersion);

        $phpModuleLoad = config('env.apache.php_module');
        $phpModuleLoad = str_replace('{version}', $phpVersion, $phpModuleLoad);
        $phpModuleLoad = str_replace('{high_version}', $highVersion, $phpModuleLoad);

        return $phpModuleLoad;
    }

    /**
     * @param string $domain
     * @return string
     */
    private function getConfPath(string $domain): string
    {
        return config('env.apache.vhosts') . DIRECTORY_SEPARATOR . $domain . '.conf';
    }

    /**
     * @return void
     */
    public function configure(): void
    {
        $this->files->ensureDirExists(config('env.apache.vhosts'));
        $this->files->ensureDirExists(config('env.logs_path'));

        $apacheConfig = $this->stubs->get(
            'httpd.conf',
            [
                'CURRENT_USER' => $_SERVER['USER'],
                'PHP_MODULE_LOADER' => config('env.apache.php_module_header'),
                'VHOSTS_PATH' => config('env.apache.vhosts'),
                'LOGS_PATH' => config('env.logs_path'),
            ]
        );
        $this->files->put(config('env.apache.config'), $apacheConfig);
        $this->includeToHttpdConfig();
    }

    /**
     * @return void
     */
    private function includeToHttpdConfig(): void
    {
        $includeConfig = sprintf('Include %s', config('env.apache.config'));
        if (strpos($this->files->get(config('env.apache.brew_config_path')), $includeConfig) === false) {
            $this->files->append(config('env.apache.brew_config_path'), PHP_EOL . $includeConfig . PHP_EOL);
        }
    }

    /**
     * @return void
     */
    public function initDefaultLocalhostVHost()
    {
        $valetDir = config('env.apache.localhost_path');
        $this->files->ensureDirExists($valetDir);
        $this->files->put($valetDir . '/index.php', '<?php' . PHP_EOL . "\t" . 'phpinfo();' . PHP_EOL);

        $this->configureVHost($valetDir, 'localhost', [], false);
    }
}
