<?php

declare(strict_types = 1);

namespace App\Commands;

use App\Command;
use App\Commands\DnsMasq\UninstallCommand as DnsMasqUninstall;
use App\Commands\MySql\UninstallCommand as MySqlUninstall;
use App\Commands\Apache\UninstallCommand as ApacheUninstall;
use App\Commands\Php\UninstallCommand as PhpUninstall;
use App\Commands\Memcached\UninstallCommand as MemcachedUninstall;
use App\Commands\Redis\UninstallCommand as RedisUninstall;
use App\Commands\MailHog\UninstallCommand as MailHogUninstall;
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

        foreach (config('env.software') as $formula) {
            $this->uninstallFormula($formula);
        }

        $this->call(DnsMasqUninstall::COMMAND);
        $this->call(MySqlUninstall::COMMAND, ['--force' => $this->option('force')]);
        $this->call(ApacheUninstall::COMMAND, ['--force' => $this->option('force')]);
        $this->call(PhpUninstall::COMMAND);
        $this->call(MemcachedUninstall::COMMAND);
        $this->call(RedisUninstall::COMMAND);
        $this->call(MailHogUninstall::COMMAND);
        $this->call(RabbitMqUninstall::COMMAND);

        if ($this->option('force')) {
            File::deleteDirectory((string)config('env.home'));
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
            Brew::ensureUninstalled((string)config('env.completion.formula'));
        });

        $this->task('Delete completion script', function () {
            File::delete(config('env.completion.brew_config_completion_path'));
        });

        $this->task('Remove include Brew completion from Bash', function () {
            $sourceText = (string)config('env.completion.brew_completion');

            $bashrcPath = (string)config('env.completion.bashrc_path');
            if (File::exists($bashrcPath)) {
                File::put($bashrcPath, str_replace($sourceText, '', File::get($bashrcPath)));
            }

            $bashProfilePath = (string)config('env.completion.bash_profile_path');
            if (File::exists($bashProfilePath)) {
                File::put($bashProfilePath, str_replace($sourceText, '', File::get($bashProfilePath)));
            }
        });
    }
}
