<?php

namespace App\Commands\MySql;

use LaravelZero\Framework\Commands\Command;

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
     * @var \App\Components\Brew\Service
     */
    private $brewService;

    /**
     * @param \App\Components\Brew\Service $brewService
     */
    public function __construct(
        \App\Components\Brew\Service $brewService
    ) {
        $this->brewService = $brewService;
        parent::__construct();
    }

    /**
     * @return void
     */
    public function handle(): void
    {
        $this->job('MySQL Start', function () {
            $this->brewService->start(config('env.mysql.formula'));
        });
    }
}
