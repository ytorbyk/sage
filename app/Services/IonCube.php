<?php

declare(strict_types = 1);

namespace App\Services;

use App\Facades\File;
use App\Facades\Stub;
use App\Facades\Cli;
use App\Facades\PeclHelper;

class IonCube
{
    const IONCUBE_EXTENSION = 'ioncube';

    const IONCUBE_PHP_NAME = 'the ionCube PHP Loader';

    /**
     * @var array
     */
    private $setting = [
        '7.3' => 'https://downloads.ioncube.com/loader_downloads/ioncube_loaders_dar_x86-64.tar.gz',
        '7.2' => 'https://downloads.ioncube.com/loader_downloads/ioncube_loaders_dar_x86-64.tar.gz',
        '7.1' => 'https://downloads.ioncube.com/loader_downloads/ioncube_loaders_dar_x86-64.tar.gz',
        '7.0' => 'https://downloads.ioncube.com/loader_downloads/ioncube_loaders_dar_x86-64.tar.gz',
        '5.6' => 'https://downloads.ioncube.com/loader_downloads/ioncube_loaders_dar_x86-64.tar.gz',
        'extension_type' => Pecl::ZEND_EXTENSION_TYPE,
        'extension_php_name' => 'the ionCube PHP Loader'
    ];

    /**
     * @return bool
     */
    public function isInstalled(): bool
    {
        return File::exists($this->extensionPath());
    }

    /**
     * @return bool
     */
    public function isEnabled(): bool
    {
        $extensions = explode("\n", Cli::runQuietly("php -m | grep '" . self::IONCUBE_PHP_NAME . "'"));
        return in_array(self::IONCUBE_PHP_NAME, $extensions);
    }

    /**
     * @return void
     * @throws \RuntimeException
     */
    public function enable(): void
    {
        if (!File::exists($this->iniPath(true))) {
            throw new \RuntimeException('IounCube config file is not found.');
        }

        File::move($this->iniPath(true), $this->iniPath());
    }

    /**
     * @return void
     */
    public function disable(): void
    {
        if (!File::exists($this->iniPath())) {
            throw new \RuntimeException('IounCube config file is not found.');
        }

        File::move($this->iniPath(), $this->iniPath(true));
    }

    /**
     * @param bool $disabled
     * @return string
     */
    private function iniPath(bool $disabled = false): string
    {
        return PeclHelper::getConfdPath() . 'ext-ioncube.ini' . ($disabled ? '.disabled' : '');
    }

    /**
     * @return string
     */
    private function extensionPath(): string
    {
        return PeclHelper::getExtensionDirectory() . DIRECTORY_SEPARATOR . self::IONCUBE_EXTENSION . '.so';
    }

    /**
     * @return void
     */
    public function configure(): void
    {
        File::ensureDirExists(PeclHelper::getConfdPath());

        File::put($this->iniPath(), Stub::get('php/ext-ioncube.ini'));
        File::delete($this->iniPath(true));
    }

    /**
     * @param string $phpVersion
     * @return void
     */
    public function install(string $phpVersion): void
    {
        if (empty($this->setting[$phpVersion])) {
            throw new \RuntimeException(sprintf('PHP v%s is not supported', $phpVersion));
        }

        File::ensureDirExists((string)config('env.tmp_path'));


        $url = $this->setting[$phpVersion];

        $urlSplit = explode('/', $url);
        $archiveName = $urlSplit[count($urlSplit) - 1];

        $extensionPath = sprintf('%s/ioncube/ioncube_loader_dar_%s.so', config('env.tmp_path'), $phpVersion);

        if (!File::exists($extensionPath)) {
            Cli::run(sprintf("cd %s && curl -O %s", config('env.tmp_path'), $url));
            Cli::run(sprintf("cd %s && tar -xvzf %s", config('env.tmp_path'), $archiveName));
        }

        if (!File::exists($extensionPath)) {
            throw new \RuntimeException('Something went wrong while IonCube installation');
        }
        File::copy($extensionPath, $this->extensionPath());
    }

    /**
     * @return void
     */
    public function uninstall(): void
    {
        File::delete($this->iniPath());
        File::delete($this->iniPath(true));
        File::delete($this->extensionPath());
    }
}
