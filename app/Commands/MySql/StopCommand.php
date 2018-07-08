<?php

namespace App\Commands\MySql;

use LaravelZero\Framework\Commands\Command;

class StopCommand extends Command
{
    const COMMAND = 'mysql:stop';

    /**
     * @var string
     */
    protected $signature = self::COMMAND;

    /**
     * @var string
     */
    protected $description = 'Stop MySQL service';

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
        $this->job('MySQL Stop', function () {
            $this->brewService->stop(config('env.mysql.formula'));
        });
    }
}
