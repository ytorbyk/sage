<?php

namespace App\Commands\Secure;

use LaravelZero\Framework\Commands\Command;

class InstallCommand extends Command
{
    const COMMAND = 'secure:install';

    /**
     * @var string
     */
    protected $signature = self::COMMAND;

    /**
     * @var string
     */
    protected $description = 'Install required software';

    /**
     * @var \App\Components\Brew
     */
    private $brew;

    /**
     * @var \App\Components\Files
     */
    private $files;

    /**
     * @param \App\Components\Brew $brew
     * @param \App\Components\Files $files
     */
    public function __construct(
        \App\Components\Brew $brew,
        \App\Components\Files $files
    ) {
        $this->brew = $brew;
        $this->files = $files;
        parent::__construct();
    }

    /**
     * @return void
     */
    public function handle(): void
    {
        $this->info('Install secure stuff:');

        $isInstalled = $this->job(sprintf('Check if [%s] is already installed', config('env.secure.formula')), function () {
            return $this->brew->isInstalled(config('env.secure.formula'));
        });

        if (!$isInstalled) {
            $this->job(sprintf('Install [%s] Brew formula', config('env.secure.formula')), function () {
                $this->brew->install('openssl');
            });
        }

        $this->job('Ensure certificate directory created', function () {
            $this->files->ensureDirExists(config('env.secure.certificates_path'));
        });
    }
}
