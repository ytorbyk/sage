<?php

declare(strict_types = 1);

namespace App\Commands\MySql;

use App\Command;
use App\Facades\Brew;
use App\Facades\File;
use App\Facades\Stub;
use App\Facades\Cli;

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
     * @return void
     */
    public function handle(): void
    {
        $this->info('Install MySQL:');

        $needInstall = $this->installFormula((string)config('env.mysql.formula'));

        $this->task(sprintf('Link [%s] formula', config('env.mysql.formula')), function () {
            Brew::link((string)config('env.mysql.formula'));
        });

        $this->task('Configure MySQL', function () {
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
        File::chmod((string)config('env.mysql.data_dir_path'), 0777);
        $mysqlConfig = Stub::get(
            'my.cnf',
            [
                'MYSQL_PASSWORD' => (string)config('env.mysql.password'),
                'LOGS_PATH' => (string)config('env.logs_path')
            ]
        );
        File::put((string)config('env.mysql.brew_config_path'), $mysqlConfig);
    }

    /**
     * @return void
     */
    private function updateSecureSettings(): void
    {
        $mysqlPasswordMessage = 'Enter previously installed MySQL root password. If was not installed any, just press enter (empty password)!';
        $this->error($mysqlPasswordMessage);
        $this->task(sprintf('Update MySQL Password to "%s"', config('env.mysql.password')), function () {
            Cli::passthru(sprintf('mysql -u root -p -e "UPDATE mysql.user SET authentication_string=PASSWORD(\'%s\') WHERE User=\'root\'"', config('env.mysql.password')));
        });

        $this->error($mysqlPasswordMessage);
        $this->task('Flush MySQL privileges', function () {
            Cli::passthru('mysql -u root -p -e "FLUSH PRIVILEGES;"');
        });

        $this->task('Delete anonymous users', function () {
            Cli::passthru('mysql -e "DELETE FROM mysql.user WHERE User=\'\';"');
        });
    }
}
