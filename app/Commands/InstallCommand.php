<?php

namespace App\Commands;

use LaravelZero\Framework\Commands\Command;
use App\Commands\DnsMasq\InstallCommand as DnsMasqInstall;
use App\Commands\MySql\InstallCommand as MySqlInstall;
use App\Commands\Apache\InstallCommand as ApacheInstall;
use App\Commands\Secure\InstallCommand as SecureInstall;
use App\Commands\Php\InstallCommand as PhpInstall;
use App\Commands\Php\SwitchCommand;

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
        $this->info('Installing and configuring local environment:');

        $brewInstalled = $this->job('Check if Brew installed', function () {
            return $this->brew->isBrewAvailable();
        });
        if (!$brewInstalled) {
            $this->info('Brew is not installed, it is required. Run the next command:');
            $this->comment('/usr/bin/ruby -e "$(curl -fsSL https://raw.githubusercontent.com/Homebrew/install/master/install)"');
            return;
        }

        $this->files->ensureDirExists(config('env.logs_path'));

        $this->call(DnsMasqInstall::COMMAND);
        $this->call(MySqlInstall::COMMAND);
        $this->call(ApacheInstall::COMMAND);
        $this->call(SecureInstall::COMMAND);
        $this->call(PhpInstall::COMMAND);

        $this->call(SwitchCommand::COMMAND, ['version' => '7.1']);

        $this->output->success(sprintf('%s is successfully installed and ready to use!', config('app.name')));
    }
}
