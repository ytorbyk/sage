<?php

declare(strict_types = 1);

namespace App\Commands\MailHog;

use App\Command;
use App\Facades\Brew;
use App\Facades\File;
use App\Facades\ApacheHelper;

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

        if (Brew::isInstalled((string)config('env.mailhog.formula'))) {
            $this->call(StopCommand::COMMAND);
            $this->uninstallFormula((string)config('env.mailhog.formula'));
        }

        ApacheHelper::deleteVHost((string)config('env.mailhog.domain'));

        $this->task('Delete MailHog Data', function () {
            $this->deleteData();
        });
    }

    /**
     * @return void
     */
    private function deleteData()
    {
        File::deleteDirectory((string)config('env.mailhog.log_path'));
    }
}
