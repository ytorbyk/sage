<?php

namespace App\Components\Site;

class IonCube
{
    const IONCUBE_EXTENSION = 'ioncube';

    const IONCUBE_PHP_NAME = 'the ionCube PHP Loader';

    /**
     * @var \App\Components\CommandLine
     */
    private $cli;

    /**
     * @var \App\Components\Files
     */
    private $files;

    /**
     * @var \App\Components\Site\Pecl
     */
    private $pecl;

    /**
     * @var \App\Components\Stubs
     */
    private $stubs;

    private $setting = [
        '7.2' => 'https://downloads.ioncube.com/loader_downloads/ioncube_loaders_dar_x86-64.tar.gz',
        '7.1' => 'https://downloads.ioncube.com/loader_downloads/ioncube_loaders_dar_x86-64.tar.gz',
        '7.0' => 'https://downloads.ioncube.com/loader_downloads/ioncube_loaders_dar_x86-64.tar.gz',
        '5.6' => 'https://downloads.ioncube.com/loader_downloads/ioncube_loaders_dar_x86-64.tar.gz',
        'extension_type' => Pecl::ZEND_EXTENSION_TYPE,
        'extension_php_name' => 'the ionCube PHP Loader'
    ];

    /**
     * @param \App\Components\CommandLine $cli
     * @param \App\Components\Files $files
     * @param \App\Components\Site\Pecl $pecl
     * @param \App\Components\Stubs $stubs
     */
    public function __construct(
        \App\Components\CommandLine $cli,
        \App\Components\Files $files,
        \App\Components\Site\Pecl $pecl,
        \App\Components\Stubs $stubs
    ) {
        $this->cli = $cli;
        $this->files = $files;
        $this->pecl = $pecl;
        $this->stubs = $stubs;
    }

    /**
     * @return bool
     */
    public function isInstalled(): bool
    {
        return $this->files->exists($this->extensionPath());
    }

    /**
     * @return bool
     */
    public function isEnabled(): bool
    {
        $extensions = explode("\n", $this->cli->runQuietly("php -m | grep '" . self::IONCUBE_PHP_NAME . "'"));
        return in_array(self::IONCUBE_PHP_NAME, $extensions);
    }

    /**
     * @return void
     * @throws \RuntimeException
     */
    public function enable(): void
    {
        if (!$this->files->exists($this->iniPath(true))) {
            throw new \RuntimeException('IounCube config file is not found.');
        }

        $this->files->move($this->iniPath(true), $this->iniPath());
    }

    /**
     * @return void
     */
    public function disable(): void
    {
        if (!$this->files->exists($this->iniPath())) {
            throw new \RuntimeException('IounCube config file is not found.');
        }

        $this->files->move($this->iniPath(), $this->iniPath(true));
    }

    /**
     * @param bool $disabled
     * @return string
     */
    private function iniPath(bool $disabled = false): string
    {
        return $this->pecl->getConfdPath() . 'ext-ioncube.ini' . ($disabled ? '.disabled' : '');
    }

    /**
     * @return string
     */
    private function extensionPath(): string
    {
        return $this->pecl->getExtensionDirectory() . DIRECTORY_SEPARATOR . self::IONCUBE_EXTENSION . '.so';
    }

    /**
     * @return void
     */
    public function configure(): void
    {
        $this->files->ensureDirExists($this->pecl->getConfdPath());

        $this->files->put($this->iniPath(), $this->stubs->get('php/ext-ioncube.ini'));
        $this->files->delete($this->iniPath(true));
    }

    /**
     * @param string $phpVersion
     * @return void
     */
    public function install(string $phpVersion): void
    {
        if (empty($this->setting[$phpVersion])) {
            throw new \RuntimeException(sprintf('PHP v%s is not supported'));
        }

        $this->files->ensureDirExists(config('env.tmp_path'));


        $url = $this->setting[$phpVersion];

        $urlSplit = explode('/', $url);
        $archiveName = $urlSplit[count($urlSplit) - 1];

        $extensionPath = sprintf('%s/ioncube/ioncube_loader_dar_%s.so', config('env.tmp_path'), $phpVersion);

        if (!$this->files->exists($extensionPath)) {
            $this->cli->run(sprintf("cd %s && curl -O %s", config('env.tmp_path'), $url));
            $this->cli->run(sprintf("cd %s && tar -xvzf %s", config('env.tmp_path'), $archiveName));
        }

        if (!$this->files->exists($extensionPath)) {
            throw new \RuntimeException('Something went wrong while IonCube installation');
        }
        $this->files->copy($extensionPath, $this->extensionPath());
    }

    /**
     * @return void
     */
    public function uninstall(): void
    {
        $this->files->delete($this->iniPath());
        $this->files->delete($this->iniPath(true));
        $this->files->delete($this->extensionPath());
    }
}
