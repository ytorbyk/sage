<?php

namespace App\Services;

use App\Facades\File;
use App\Facades\Stub;
use App\Facades\Cli;

class Pecl
{
    const XDEBUG_EXTENSION = 'xdebug';

    const NORMAL_EXTENSION_TYPE = 'extension';
    const ZEND_EXTENSION_TYPE = 'zend_extension';

    /**
     * @var array
     */
    private $extensions = [
        self::XDEBUG_EXTENSION => [
            '5.6' => '2.5.5',
            'extension_type' => self::ZEND_EXTENSION_TYPE
        ]
    ];

    /**
     * @param string $extension
     * @return bool
     */
    public function isInstalled(string $extension): bool
    {
        return strpos(Cli::runQuietly('pecl list | grep ' . $extension), $extension) !== false;
    }

    /**
     * @param string $extension
     * @return bool
     */
    public function isEnabled(string $extension): bool
    {
        $extensions = explode("\n", Cli::runQuietly("php -m | grep '$extension'"));
        return in_array($extension, $extensions);
    }

    /**
     * @param string $extension
     * @return void
     */
    public function enable(string $extension): void
    {
        if (!File::exists($this->iniPath($extension, true))) {
            throw new \RuntimeException($extension . ' config file is not found.');
        }
        File::move($this->iniPath($extension, true), $this->iniPath($extension));
    }

    /**
     * @param string $extension
     * @return void
     */
    public function disable(string $extension): void
    {
        if (!File::exists($this->iniPath($extension))) {
            throw new \RuntimeException($extension . ' config file is not found.');
        }
        File::move($this->iniPath($extension), $this->iniPath($extension, true));
    }

    /**
     * @param string $extension
     * @param bool $disabled
     * @return string
     */
    public function iniPath(string $extension, bool $disabled = false): string
    {
        return $this->getConfDPath() . "ext-{$extension}.ini" . ($disabled ? '.disabled' : '');
    }

    /**
     * @return string
     */
    public function getConfDPath(): string
    {
        return dirname($this->getPhpIniPath()) . '/conf.d/';
    }

    /**
     * @return string
     */
    public function getPhpIniPath(): string
    {
        return str_replace("\n", '', Cli::run('pecl config-get php_ini'));
    }

    /**
     * @return string
     */
    public function getExtensionDirectory(): string
    {
        return str_replace("\n", '', Cli::run('pecl config-get ext_dir'));
    }

    /**
     * @return void
     */
    public function updatePeclChannel(): void
    {
        Cli::run('pecl channel-update pecl.php.net');
    }

    /**
     * @param string $extension
     * @param string $phpVersion
     * @return void
     */
    public function install(string $extension, string $phpVersion): void
    {
        $extensionVersion = isset($this->extensions[$extension][$phpVersion]) ? $this->extensions[$extension][$phpVersion] : null;
        $extensionVersion = $extensionVersion === null ? $extension : $extension . '-' . $extensionVersion;

        Cli::run("pecl uninstall -r $extension");
        $result = Cli::run("pecl install $extensionVersion");

        if (!preg_match("/Installing '(.*{$extension}.so)'/", $result)) {
            throw new \DomainException("Could not find installation path for: $extension\n\n$result");
        }

        if (strpos($result, "Error:")) {
            throw new \DomainException("Installation path found, but installation failed:\n\n$result");
        }
    }

    /**
     * @param string $extension
     * @return void
     */
    public function configure($extension): void
    {
        $stubName = "php/ext-{$extension}.ini";

        if (Stub::isExist($stubName)) {
            $this->removeIniDefinition($extension);
            File::put($this->iniPath($extension), Stub::get($stubName));
        }
    }

    /**
     * Replace and remove all directives of the .so file for the given extension within the php.ini file.
     *
     * @param $extension
     *    The extension key name.
     */
    private function removeIniDefinition($extension)
    {
        $phpIniPath = $this->getPhpIniPath();
        $phpIniFile = File::get($phpIniPath);
        $phpIniFile = preg_replace('/;?(zend_extension|extension)\=".*' . $extension . '.so"/', '', $phpIniFile);
        File::put($phpIniPath, $phpIniFile);
    }

    /**
     * @param string $extension
     * @param string $phpVersion
     * @return void
     */
    public function uninstall(string $extension, string $phpVersion): void
    {
        $extensionVersion = isset($this->extensions[$extension][$phpVersion]) ? $this->extensions[$extension][$phpVersion] : null;
        $extensionVersion = $extensionVersion === null ? $extension : $extension . '-' . $extensionVersion;

        Cli::run("pecl uninstall $extensionVersion");
    }

    /**
     * @param string $phpVersion
     * @return void
     */
    public function deleteConfigs(string $phpVersion): void
    {
        $pearDirSuffix = config('env.php.main_version') === $phpVersion ? '' : '@' . $phpVersion;
        File::deleteDirectory(config('env.php.brew_pear_path') . $pearDirSuffix);
    }
}
