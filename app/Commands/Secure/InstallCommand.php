<?php

declare(strict_types = 1);

namespace App\Commands\Secure;

use App\Command;
use App\Facades\File;

class InstallCommand extends Command
{
    const COMMAND = 'secure:install';

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
        $this->info('Install secure stuff:');

        $this->installFormula((string)config('env.secure.formula'));

        $this->task('Ensure certificate directory created', function () {
            File::ensureDirExists((string)config('env.secure.certificates_path'));
        });
    }
}
