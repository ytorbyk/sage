<?php

namespace App\Commands\MySql;

use App\Command;
use App\Facades\BrewService;

class StartCommand extends Command
{
    const COMMAND = 'mysql:start';

    /**
     * @var string
     */
    protected $signature = self::COMMAND;

    /**
     * @var string
     */
    protected $description = 'Start MySQL service';

    /**
     * @return void
     */
    public function handle(): void
    {
        $this->task('MySQL Start', function () {
            BrewService::start(config('env.mysql.formula'));
        });
    }
}
