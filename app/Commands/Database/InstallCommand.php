<?php

namespace App\Commands\Database;

use LaravelZero\Framework\Commands\Command;

class InstallCommand extends Command
{
    const COMMAND = 'db:install';

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
        $this->info('Install DB dump stuff:');

        $progressFormula = config('env.progress.formula');

        $isInstalled = $this->job(sprintf('Check if [%s] is already installed', $progressFormula), function () use ($progressFormula) {
            return $this->brew->isInstalled($progressFormula);
        });

        if (!$isInstalled) {
            $this->job(sprintf('Install [%s] Brew formula', $progressFormula), function () use ($progressFormula) {
                $this->brew->install($progressFormula);
            });
        }

        $this->job('Ensure DB dumps directory created', function () {
            $this->files->ensureDirExists(config('env.db.dump_path'));
        });
    }
}
