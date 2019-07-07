<?php

namespace App\Commands;

use App\Command;
use App\Commands\DnsMasq\UninstallCommand as DnsMasqUninstall;
use App\Commands\MySql\UninstallCommand as MySqlUninstall;
use App\Commands\Apache\UninstallCommand as ApacheUninstall;
use App\Commands\Php\UninstallCommand as PhpUninstall;
use App\Commands\MailHog\UninstallCommand as MailHogUninstall;
use App\Commands\ElasticSearch\UninstallCommand as ElasticSearchUninstall;
use App\Commands\Kibana\UninstallCommand as KibanaUninstall;
use App\Commands\RabbitMq\UninstallCommand as RabbitMqUninstall;
use App\Facades\Brew;
use App\Facades\File;

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
     * @return void
     */
    public function handle(): void
    {
        $this->info('Uninstalling local environment:');

        $brewInstalled = $this->task('Check if Brew installed', function () {
            return Brew::isBrewAvailable();
        });
        if (!$brewInstalled) {
            $this->info('Brew is not installed. Nothing to uninstall!');
        }

        $this->call(DnsMasqUninstall::COMMAND);
        $this->call(MySqlUninstall::COMMAND, ['--force' => $this->option('force')]);
        $this->call(ApacheUninstall::COMMAND, ['--force' => $this->option('force')]);
        $this->call(PhpUninstall::COMMAND);
        $this->call(MailHogUninstall::COMMAND);
        $this->call(ElasticSearchUninstall::COMMAND);
        $this->call(KibanaUninstall::COMMAND);
        $this->call(RabbitMqUninstall::COMMAND);

        foreach (config('env.software') as $formula) {
            $this->uninstallFormula($formula);
        }

        if ($this->option('force')) {
            File::deleteDirectory(config('env.home'));
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

        $this->task(sprintf('Uninstall [%s]', config('env.completion.formula')), function () {
            Brew::ensureUninstalled(config('env.completion.formula'));
        });

        $this->task('Delete completion script', function () {
            File::delete(config('env.completion.brew_config_completion_path'));
        });

        $this->task('Remove include Brew completion from Bash', function () {
            $sourceText = config('env.completion.brew_completion');

            $bashrcPath = config('env.completion.bashrc_path');
            if (File::exists($bashrcPath)) {
                File::put($bashrcPath, str_replace($sourceText, '', File::get($bashrcPath)));
            }

            $bashProfilePath = config('env.completion.bash_profile_path');
            if (File::exists($bashProfilePath)) {
                File::put($bashProfilePath, str_replace($sourceText, '', File::get($bashProfilePath)));
            }
        });
    }
}
