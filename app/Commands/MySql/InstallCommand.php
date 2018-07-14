<?php

namespace App\Commands\MySql;

use LaravelZero\Framework\Commands\Command;

class InstallCommand extends Command
{
    const COMMAND = 'mysql:install';

    /**
     * @var string
     */
    protected $signature = self::COMMAND;

    /**
     * @var string
     */
    protected $description = 'Install and configure MySQL';

    /**
     * @var \App\Components\Brew
     */
    private $brew;

    /**
     * @var \App\Components\Files
     */
    private $files;

    /**
     * @var \App\Components\Stubs
     */
    private $stubs;

    /**
     * @var \App\Components\CommandLine
     */
    private $cli;

    /**
     * @param \App\Components\Brew $brew
     * @param \App\Components\Files $files
     * @param \App\Components\Stubs $stubs
     * @param \App\Components\CommandLine $cli
     */
    public function __construct(
        \App\Components\Brew $brew,
        \App\Components\Files $files,
        \App\Components\Stubs $stubs,
        \App\Components\CommandLine $cli
    ) {
        $this->brew = $brew;
        $this->files = $files;
        $this->stubs = $stubs;
        $this->cli = $cli;
        parent::__construct();
    }

    /**
     * @return void
     */
    public function handle(): void
    {
        $this->info('Install MySQL:');

        $needInstall = $this->job('Need to be installed?', function () {
            return !$this->brew->isInstalled(config('env.mysql.formula')) ?: 'Installed. Skip';
        });
        if ($needInstall === true) {
            $this->job(sprintf('Install [%s] Brew formula', config('env.mysql.formula')), function () {
                $this->brew->install(config('env.mysql.formula'));
            });
        }

        $this->job(sprintf('Link [%s] formula', config('env.mysql.formula')), function () {
            $this->brew->link(config('env.mysql.formula'));
        });

        $this->job('Configure MySQL', function () {
            $this->configureMySQL();
        });

        if ($needInstall === true) {
            $this->call(RestartCommand::COMMAND);
            $this->updateSecureSettings();
        }

        $this->call(RestartCommand::COMMAND);
    }

    /**
     * @return void
     */
    private function configureMySQL(): void
    {
        $this->files->chmod(config('env.mysql.data_dir_path'), 0777);
        $mysqlConfig = $this->stubs->get(
            '/my.cnf',
            [
                'MYSQL_PASSWORD' => config('env.mysql.password'),
                'LOGS_PATH' => config('env.logs_path')
            ]
        );
        $this->files->put(config('env.mysql.brew_config_path'), $mysqlConfig);
    }

    /**
     * @return void
     */
    private function updateSecureSettings(): void
    {
        $mysqlPasswordMessage = 'Enter previously installed MySQL root password. If was not installed any, just press enter (empty password)!';
        $this->output->writeln('<fg=red>' . $mysqlPasswordMessage . '</>');
        $this->job(sprintf('Update MySQL Password to "%s"', config('env.mysql.password')), function () {
            $this->cli->run(sprintf('mysql -u root -p -e "UPDATE mysql.user SET authentication_string=PASSWORD(\'%s\') WHERE User=\'root\'"', config('env.mysql.password')));
        });

        $this->output->writeln('<fg=red>' . $mysqlPasswordMessage . '</>');
        $this->job('Flush MySQL privileges', function () {
            $this->cli->run('mysql -u root -p -e "FLUSH PRIVILEGES;"');
        });

        $this->job('Delete anonymous users', function () {
            $this->cli->run('mysql -e "DELETE FROM mysql.user WHERE User=\'\';"');
        });
    }
}
