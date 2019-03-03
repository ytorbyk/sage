<?php

namespace App\Commands\MailHog;

use App\Command;
use App\Facades\Brew;
use App\Facades\File;

class UninstallCommand extends Command
{
    const COMMAND = 'mailhog:uninstall';

    /**
     * @var string
     */
    protected $signature = self::COMMAND;

    /**
     * @var string
     */
    protected $description = 'Uninstall MailHog';

    /**
     * @return void
     */
    public function handle(): void
    {
        $this->info('Uninstall MailHog:');

        if (Brew::isInstalled(config('env.mailhog.formula'))) {
            $this->call(StopCommand::COMMAND);
            $this->uninstallFormula(config('env.mailhog.formula'));
        }

        $this->task('Delete MailHog Data', function () {
            $this->deleteData();
        });
    }

    /**
     * @return void
     */
    private function deleteData()
    {
        File::deleteDirectory(config('env.mailhog.log_path'));
    }
}
