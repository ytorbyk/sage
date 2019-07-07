<?php

declare(strict_types = 1);

namespace App\Services;

use App\Facades\Secure;
use App\Facades\Stub;
use App\Facades\File;

class Apache
{
    /**
     * @param string $domain
     * @return void
     */
    public function deleteVHost(string $domain): void
    {
        File::delete($this->getConfPath($domain));
    }

    /**
     * @param string $domain
     * @param string $documentRoot
     * @param string[] $aliases
     * @param bool $secure
     * @return void
     */
    public function configureVHost(string $domain, string $documentRoot, array $aliases = [], bool $secure = true): void
    {
        $this->configureHost($domain, $documentRoot, '', $aliases, $secure);
    }

    /**
     * @param string $domain
     * @param string $port
     * @param string[] $aliases
     * @param bool $secure
     * @return void
     */
    public function configureProxyVHost(string $domain, string $port, array $aliases = [], bool $secure = true): void
    {
        $this->configureHost($domain, '', $port, $aliases, $secure);
    }

    /**
     * @param string $domain
     * @param string $documentRoot
     * @param string $port
     * @param array $aliases
     * @param bool $secure
     * @return void
     */
    private function configureHost(string $domain, string $documentRoot = '', string $port = '', array $aliases = [], bool $secure = true)
    {
        $vhostTemplate = empty($documentRoot) && !empty($port) ? 'httpd-proxy-vhost.conf' : 'httpd-vhost.conf';
        $sslVhostTemplate = empty($documentRoot) && !empty($port) ? 'httpd-proxy-vhost-ssl.conf' : 'httpd-vhost-ssl.conf';

        File::ensureDirExists((string)config('env.apache.vhosts'));
        $this->deleteVHost($domain);

        $serverAliases = !empty($aliases)
            ? 'ServerAlias ' . implode(' ', $aliases)
            : '';

        $virtualHostSsl = '';
        if ($secure && Secure::canSecure($domain)) {
            $virtualHostSsl = Stub::get(
                $sslVhostTemplate,
                [
                    'DOMAIN' => $domain,
                    'SERVER_ALIAS' => $serverAliases,
                    'DOCUMENT_ROOT' => $documentRoot,
                    'PORT' => $port,
                    'LOGS_PATH' => config('env.logs_path'),
                    'CERTIFICATE_CRT' => Secure::getFilePath($domain, 'crt'),
                    'CERTIFICATE_KEY' => Secure::getFilePath($domain, 'key')
                ]
            );
        }

        $vhostConfig = Stub::get(
            $vhostTemplate,
            [
                'DOMAIN' => $domain,
                'SERVER_ALIAS' => $serverAliases,
                'DOCUMENT_ROOT' => $documentRoot,
                'PORT' => $port,
                'LOGS_PATH' => config('env.logs_path'),
                'VIRTUAL_HOST_SSL' => $virtualHostSsl,
            ]
        );
        File::put($this->getConfPath($domain), $vhostConfig);
    }

    /**
     * @return void
     */
    public function unlinkPhp(): void
    {
        $config = File::get((string)config('env.apache.config'));
        foreach (config('env.php.versions') as $phpVersion) {
            $config = $this->removePhpVersion($config, $phpVersion);
        }
        File::put((string)config('env.apache.config'), $config);
    }

    /**
     * @param string $version
     * @return void
     */
    public function linkPhp(string $version): void
    {
        $this->unlinkPhp();

        $config = File::get((string)config('env.apache.config'));
        $phpModuleHeader = config('env.apache.php_module_header') . PHP_EOL;
        if (strpos($config, $phpModuleHeader) === false) {
            throw new \RuntimeException('Apache config is broken');
        }

        $phpModuleLoad = $this->getPhpModuleLoad($version);
        $config = str_replace($phpModuleHeader, $phpModuleHeader . $phpModuleLoad . PHP_EOL, $config);
        File::put((string)config('env.apache.config'), $config);
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
        $configFilename = $domain === 'localhost' ? '00-default' : $domain;
        return config('env.apache.vhosts') . DIRECTORY_SEPARATOR . $configFilename . '.conf';
    }

    /**
     * @return void
     */
    public function configure(): void
    {
        File::ensureDirExists((string)config('env.apache.vhosts'));
        File::ensureDirExists((string)config('env.logs_path'));

        $apacheConfig = Stub::get(
            'httpd.conf',
            [
                'CURRENT_USER' => $_SERVER['USER'],
                'PHP_MODULE_LOADER' => config('env.apache.php_module_header'),
                'VHOSTS_PATH' => config('env.apache.vhosts'),
                'LOGS_PATH' => config('env.logs_path'),
            ]
        );
        File::put((string)config('env.apache.config'), $apacheConfig);
        $this->includeToHttpdConfig();
    }

    /**
     * @return void
     */
    private function includeToHttpdConfig(): void
    {
        $includeConfig = sprintf('Include %s', config('env.apache.config'));
        if (strpos(File::get((string)config('env.apache.brew_config_path')), $includeConfig) === false) {
            File::append((string)config('env.apache.brew_config_path'), PHP_EOL . $includeConfig . PHP_EOL);
        }
    }

    /**
     * @return void
     */
    public function initDefaultLocalhostVHost()
    {
        $valetDir = (string)config('env.apache.localhost_path');
        File::ensureDirExists($valetDir);
        File::put($valetDir . '/index.php', Stub::get('localhost/index.php'));
        File::put($valetDir . '/no-entry.jpg', Stub::get('localhost/no-entry.jpg'));

        $this->configureVHost('localhost', $valetDir, [], false);
    }
}
