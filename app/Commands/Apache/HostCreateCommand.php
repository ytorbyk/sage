<?php

namespace App\Commands\Apache;

use App\Command;
use App\Commands\Secure\GenerateCommand;
use App\Facades\File;
use App\Facades\Secure;
use App\Facades\ApacheHelper;

class HostCreateCommand extends Command
{
    const COMMAND = 'apache:host';

    /**
     * @var string
     */
    protected $signature = self::COMMAND
        . ' {domain} {aliases?*}'
        . ' {--p|path= : Document root path}'
        . ' {--not-secure : Do not create secure virtual host}';

    /**
     * @var string
     */
    protected $description = 'Create Apache Virtual Host';

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

        if (Secure::canSecure($domain) && $secure) {
            $this->call(GenerateCommand::COMMAND, ['domain' => $domain, 'aliases' => $aliases]);
        }

        $this->task('Create Apache Virtual Host', function () use ($hostPath, $secure) {
            ApacheHelper::configureVHost(
                $hostPath,
                $this->argument('domain'),
                $this->argument('aliases'),
                $secure
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
        return File::exists($path) && File::isDirectory($path);
    }
}
