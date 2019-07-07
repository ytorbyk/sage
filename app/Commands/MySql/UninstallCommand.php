<?php

declare(strict_types = 1);

namespace App\Commands\MySql;

use App\Command;
use App\Facades\File;

class UninstallCommand extends Command
{
    const COMMAND = 'mysql:uninstall';

    /**
     * @var string
     */
    protected $signature = self::COMMAND
        . ' {--f|force : Delete MySQL Data}';

    /**
     * @var string
     */
    protected $description = 'Uninstall MySQL';

    /**
     * @return void
     */
    public function handle(): void
    {
        $this->info('Uninstall MySQL:');

        $this->uninstallFormula((string)config('env.mysql.formula'));

        $this->task('Delete configuration', function () {
            File::delete(config('env.mysql.brew_config_path'));
        });

        if ($this->option('force')) {
            $this->task('Delete Data', function () {
                File::deleteDirectory((string)config('env.mysql.data_dir_path'));
            });
        }
    }
}
