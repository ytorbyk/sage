<?php

declare(strict_types = 1);

namespace App\Commands\Database;

use App\Command;
use App\Facades\File;

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
     * @return void
     */
    public function handle(): void
    {
        $this->info('Install DB dump stuff:');

        $this->installFormula((string)config('env.progress.formula'));

        $this->task('Ensure DB dumps directory created', function () {
            File::ensureDirExists((string)config('env.db.dump_path'));
        });

        $this->task('Setup Locale setting for Bash', function () {

            $bashrcPath = (string)config('env.completion.bashrc_path');
            $bashProfilePath = (string)config('env.completion.bash_profile_path');

            if ((!File::exists($bashrcPath) || strpos(File::get($bashrcPath), 'LC_ALL') === false)
                && (!File::exists($bashProfilePath) || strpos(File::get($bashProfilePath), 'LC_ALL') === false)
            ) {
                File::append(
                    $bashProfilePath,
                    PHP_EOL . 'export LC_ALL=en_US.UTF-8' . PHP_EOL . 'export LANG=en_US.UTF-8' . PHP_EOL
                );
            }
        });
    }
}
