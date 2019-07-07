<?php

declare(strict_types = 1);

namespace App\Commands\ElasticSearch;

use App\Command;
use App\Facades\Brew;
use App\Facades\File;

class UninstallCommand extends Command
{
    const COMMAND = 'elasticsearch:uninstall';

    /**
     * @var string
     */
    protected $signature = self::COMMAND;

    /**
     * @var string
     */
    protected $description = 'Uninstall ElasticSearch';

    /**
     * @return void
     */
    public function handle(): void
    {
        $this->info('Uninstall ElasticSearch:');

        if (Brew::isInstalled((string)config('env.elasticsearch.formula'))) {
            $this->call(StopCommand::COMMAND);
            $this->uninstallFormula((string)config('env.elasticsearch.formula'));
        }

        $this->task('Delete ElasticSearch Data', function () {
            $this->deleteData();
        });
    }

    /**
     * @return void
     */
    private function deleteData()
    {
        File::deleteDirectory((string)config('env.elasticsearch.brew_config_dir_path'));
        File::deleteDirectory((string)config('env.elasticsearch.data_dir_path'));
        File::deleteDirectory((string)config('env.elasticsearch.log_dir_path'));
    }
}
