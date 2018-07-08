<?php

namespace App\Components\Site;

class Pecl
{
    const XDEBUG_EXTENSION = 'xdebug';
    const APCU_EXTENSION = 'apcu';

    const NORMAL_EXTENSION_TYPE = 'extension';
    const ZEND_EXTENSION_TYPE = 'zend_extension';

    /**
     * @var \App\Components\CommandLine
     */
    private $cli;

    /**
     * @var \App\Components\Files
     */
    private $files;

    /**
     * @var \App\Components\Stubs
     */
    private $stubs;

    /**
     * @var array
     */
    private $extensions = [
        self::XDEBUG_EXTENSION => [
            '5.6' => '2.5.5',
            'extension_type' => self::ZEND_EXTENSION_TYPE
        ],
        self::APCU_EXTENSION => [
            '5.6' => '4.0.11',
            'extension_type' => self::NORMAL_EXTENSION_TYPE
        ]
    ];

    /**
     * @param \App\Components\CommandLine $cli
     * @param \App\Components\Files $files
     * @param \App\Components\Stubs $stubs
     */
    public function __construct(
        \App\Components\CommandLine $cli,
        \App\Components\Files $files,
        \App\Components\Stubs $stubs
    ) {
        $this->cli = $cli;
        $this->files = $files;
        $this->stubs = $stubs;
    }

    /**
     * @param string $extension
     * @return bool
     */
    public function isInstalled(string $extension): bool
    {
        return strpos($this->cli->runQuietly('pecl list | grep ' . $extension), $extension) !== false;
    }

    /**
     * @param string $extension
     * @return bool
     */
    public function isEnabled(string $extension): bool
    {
        $extensions = explode("\n", $this->cli->runQuietly("php -m | grep '$extension'"));
        return in_array($extension, $extensions);
    }

    /**
     * @param string $extension
     * @return void
     */
    public function enable(string $extension): void
    {
        if (!$this->files->exists($this->iniPath($extension, true))) {
            throw new \RuntimeException($extension . ' config file is not found.');
        }
        $this->files->move($this->iniPath($extension, true), $this->iniPath($extension));
    }

    /**
     * @param string $extension
     * @return void
     */
    public function disable(string $extension): void
    {
        if (!$this->files->exists($this->iniPath($extension))) {
            throw new \RuntimeException($extension . ' config file is not found.');
        }
        $this->files->move($this->iniPath($extension), $this->iniPath($extension, true));
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
        return str_replace("\n", '', $this->cli->run('pecl config-get php_ini'));
    }

    /**
     * @return string
     */
    public function getExtensionDirectory(): string
    {
        return str_replace("\n", '', $this->cli->run('pecl config-get ext_dir'));
    }

    /**
     * @return void
     */
    public function updatePeclChannel()
    {
        $this->cli->run('pecl channel-update pecl.php.net');
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

        $result = $this->cli->run("pecl install $extensionVersion");

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
    public function configure($extension)
    {
        $stubName = "php/ext-{$extension}.ini";

        if ($this->stubs->isExist($stubName)) {
            $this->removeIniDefinition($extension);
            $this->files->put($this->iniPath($extension), $this->stubs->get($stubName));
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
        $phpIniFile = $this->files->get($phpIniPath);
        $phpIniFile = preg_replace('/;?(zend_extension|extension)\=".*' . $extension . '.so"/', '', $phpIniFile);
        $this->files->put($phpIniPath, $phpIniFile);
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

        $this->cli->run("pecl uninstall $extensionVersion");
    }

    /**
     * @param string $phpVersion
     * @return void
     */
    public function deleteConfigs(string $phpVersion): void
    {
        $pearDirSuffix = config('env.php.main_version') === $phpVersion ? '' : '@' . $phpVersion;
        $this->files->deleteDirectory(config('env.php.brew_pear_path') . $pearDirSuffix);
    }
}
