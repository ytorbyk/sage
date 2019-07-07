<?php

declare(strict_types = 1);

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
            try {
                BrewService::start((string)config('env.mysql.formula'));
            } catch (\Exception $e) {
                return $e->getMessage();
            }
        });
    }
}
