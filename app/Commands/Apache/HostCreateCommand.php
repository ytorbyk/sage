<?php

namespace App\Commands\Apache;

use LaravelZero\Framework\Commands\Command;
use App\Commands\Secure\GenerateCommand;

class HostCreateCommand extends Command
{
    const COMMAND = 'apache:host';

    /**
     * @var string
     */
    protected $signature = self::COMMAND . ' {domain} {aliases?*}
        {--p|path= : Document root path}
        {--not-secure : Do not create secure virtual host}';

    /**
     * @var string
     */
    protected $description = 'Create Apache Virtual Host';

    /**
     * @var \App\Components\Site\Apache
     */
    private $apache;

    /**
     * @var \App\Components\Site\Secure
     */
    private $siteSecure;

    /**
     * @var \App\Components\Files
     */
    private $files;

    /**
     * @param \App\Components\Site\Apache $apache
     * @param \App\Components\Site\Secure $siteSecure
     * @param \App\Components\Files $files
     */
    public function __construct(
        \App\Components\Site\Apache $apache,
        \App\Components\Site\Secure $siteSecure,
        \App\Components\Files $files
    ) {
        $this->apache = $apache;
        $this->siteSecure = $siteSecure;
        $this->files = $files;
        parent::__construct();
    }

    /**
     * @return void
     */
    public function handle(): void
    {
        $domain = $this->argument('domain');
        $aliases = $this->argument('aliases');
        $secure = !$this->option('not-secure');
        $path = $this->option('path');

        $hostPath = $this->getHostPath($path);
        if (!$this->verifyPath($hostPath)) {
            $this->error('Passed path does not exist or not a folder: ' . $hostPath);
            return;
        }

        if ($this->siteSecure->canSecure($domain) && $secure) {
            $this->call(GenerateCommand::COMMAND, ['domain' => $domain, 'aliases' => $aliases]);
        }

        $this->job('Create Apache Virtual Host', function () use ($hostPath) {
            $this->apache->configureVHost(
                $hostPath,
                $this->argument('domain'),
                $this->argument('aliases'),
                $this->option('secure')
            );
        });
    }

    /**
     * @param string $path
     * @return string
     */
    private function getHostPath(?string $path): string
    {
        if (null === $path) {
            $hostPath = getcwd();
        } elseif (strpos($path, '/') === 0) {
            $hostPath = $path;
        } else {
            $hostPath = getcwd() . DIRECTORY_SEPARATOR . ltrim($path, DIRECTORY_SEPARATOR);
        }

        return $hostPath;
    }

    /**
     * @param string $path
     * @return bool
     */
    private function verifyPath(string $path): bool
    {
        return $this->files->exists($path) && $this->files->isDirectory($path);
    }
}
