<?php

namespace App\Commands;

use LaravelZero\Framework\Commands\Command;
use App\Commands\DnsMasq\UninstallCommand as DnsMasqUninstall;
use App\Commands\MySql\UninstallCommand as MySqlUninstall;
use App\Commands\Apache\UninstallCommand as ApacheUninstall;
use App\Commands\Php\UninstallCommand as PhpUninstall;

class UninstallCommand extends Command
{
    /**
     * @var string
     */
    protected $signature = 'env:uninstall {--f|force : Delete configuration and MySQL Data}';

    /**
     * @var string
     */
    protected $description = 'Uninstall all installed environment via Brew';

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
        $this->info('Uninstalling local environment:');

        $brewInstalled = $this->job('Check if Brew installed', function () {
            return $this->brew->isBrewAvailable();
        });
        if (!$brewInstalled) {
            $this->info('Brew is not installed. Nothing to uninstall!');
        }

        $this->call(DnsMasqUninstall::COMMAND);
        $this->call(MySqlUninstall::COMMAND, ['--force' => $this->option('force')]);
        $this->call(ApacheUninstall::COMMAND);
        $this->call(PhpUninstall::COMMAND);

        if ($this->option('force')) {
            $this->files->deleteDirectory(config('env.home'));
            $this->uninstallCompletion();
        }

        $this->output->success(sprintf('%s is successfully uninstalled :(', config('app.name')));
    }

    /**
     * @return void
     */
    private function uninstallCompletion(): void
    {
        $this->info('Uninstall completion:');

        $this->job(sprintf('Uninstall [%s]', config('env.completion.formula')), function () {
            $this->brew->ensureUninstalled(config('env.completion.formula'));
        });

        $this->job('Delete completion script', function () {
            $this->files->delete(config('env.completion.brew_config_completion_path'));
        });

        $this->job('Remove include Brew completion from Bash', function () {
            $sourceText = config('env.completion.brew_completion');

            $bashrcPath = config('env.completion.bashrc_path');
            if ($this->files->exists($bashrcPath)) {
                $this->files->put($bashrcPath, str_replace($sourceText, '', $this->files->get($bashrcPath)));
            }

            $bashProfilePath = config('env.completion.bash_profile_path');
            if ($this->files->exists($bashProfilePath)) {
                $this->files->put($bashProfilePath, str_replace($sourceText, '', $this->files->get($bashProfilePath)));
            }
        });
    }
}
