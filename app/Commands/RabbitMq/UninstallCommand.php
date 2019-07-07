<?php

namespace App\Commands\RabbitMq;

use App\Command;
use App\Facades\Brew;
use App\Facades\File;
use App\Facades\ApacheHelper;

class UninstallCommand extends Command
{
    const COMMAND = 'rabbitmq:uninstall';

    /**
     * @var string
     */
    protected $signature = self::COMMAND;

    /**
     * @var string
     */
    protected $description = 'Uninstall RabbitMq';

    /**
     * @return void
     */
    public function handle(): void
    {
        $this->info('Uninstall RabbitMq:');

        if (Brew::isInstalled(config('env.rabbitmq.formula'))) {
            $this->call(StopCommand::COMMAND);
            $this->uninstallFormula(config('env.rabbitmq.formula'));
        }

        ApacheHelper::deleteVHost((string)config('env.rabbitmq.domain'));

        $this->task('Delete RabbitMq Data', function () {
            $this->deleteData();
        });
    }

    /**
     * @return void
     */
    private function deleteData()
    {
        File::deleteDirectory(config('env.rabbitmq.brew_config_dir_path'));
        File::deleteDirectory(config('env.rabbitmq.brew_lib_dir_path'));
        File::deleteDirectory(config('env.rabbitmq.log_dir_path'));
    }
}
