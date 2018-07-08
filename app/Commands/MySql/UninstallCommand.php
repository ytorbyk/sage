<?php

namespace App\Commands\MySql;

use LaravelZero\Framework\Commands\Command;

class UninstallCommand extends Command
{
    const COMMAND = 'mysql:uninstall';

    /**
     * @var string
     */
    protected $signature = self::COMMAND . ' {--f|force : Delete MySQL Data}';

    /**
     * @var string
     */
    protected $description = 'Uninstall MySQL';

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
        $this->info('Uninstall MySQL:');

        $needUninstall = $this->job('Need to be uninstalled?', function () {
            return $this->brew->isInstalled(config('env.mysql.formula')) ?: 'Uninstalled. Skip';
        });
        if ($needUninstall === true) {
            $this->call(StopCommand::COMMAND);

            $this->job(sprintf('Uninstall [%s] Brew formula', config('env.mysql.formula')), function () {
                $this->brew->uninstall(config('env.mysql.formula'));
            });
        }

        $this->job('Delete configuration', function () {
            $this->deleteMySqlConfig();
        });

        if ($this->option('force')) {
            $this->job('Delete Data', function () {
                $this->deleteMySqlData();
            });
        }
    }

    /**
     * @return void
     */
    private function deleteMySqlConfig(): void
    {
        $this->files->delete(config('env.mysql.brew_config_path'));
    }

    /**
     * @return void
     */
    private function deleteMySqlData(): void
    {
        $this->files->deleteDirectory(config('env.mysql.data_dir_path'));
    }
}
