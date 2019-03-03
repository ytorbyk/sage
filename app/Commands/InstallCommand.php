<?php

namespace App\Commands;

use App\Command;
use App\Commands\DnsMasq\InstallCommand as DnsMasqInstall;
use App\Commands\MySql\InstallCommand as MySqlInstall;
use App\Commands\Database\InstallCommand as DatabaseInstall;
use App\Commands\Apache\InstallCommand as ApacheInstall;
use App\Commands\Secure\InstallCommand as SecureInstall;
use App\Commands\Php\InstallCommand as PhpInstall;
use App\Commands\Php\SwitchCommand;
use App\Commands\MailHog\InstallCommand as MailHogInstall;
use App\Commands\ElasticSearch\InstallCommand as ElasticSearchInstall;
use App\Commands\RabbitMq\InstallCommand as RabbitMqInstall;

use App\Facades\Brew;
use App\Facades\File;

class InstallCommand extends Command
{
    /**
     * @var string
     */
    protected $signature = 'env:install';

    /**
     * @var string
     */
    protected $description = 'Install and configure required environment via Brew';

    /**
     * @return void
     */
    public function handle(): void
    {
        $this->info('Installing and configuring local environment:');

        $brewInstalled = $this->task('Check if Brew installed', function () {
            return Brew::isBrewAvailable();
        });
        if (!$brewInstalled) {
            $this->info('Brew is not installed, it is required. Run the next command:');
            $this->comment('/usr/bin/ruby -e "$(curl -fsSL https://raw.githubusercontent.com/Homebrew/install/master/install)"');
            return;
        }

        File::ensureDirExists(config('env.home_public'));
        File::ensureDirExists(config('env.logs_path'));

        foreach (config('env.software') as $formula) {
            $this->installFormula($formula);
        }

        $this->call(DnsMasqInstall::COMMAND);
        $this->call(MySqlInstall::COMMAND);
        $this->call(DatabaseInstall::COMMAND);
        $this->call(ApacheInstall::COMMAND);
        $this->call(SecureInstall::COMMAND);
        $this->call(PhpInstall::COMMAND);

        $this->call(SwitchCommand::COMMAND, ['version' => '7.1']);

        $this->call(MailHogInstall::COMMAND);
        $this->call(ElasticSearchInstall::COMMAND);
        $this->call(RabbitMqInstall::COMMAND);

        $this->output->success(sprintf('%s is successfully installed and ready to use!', config('app.name')));
    }
}
