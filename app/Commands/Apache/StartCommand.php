<?php

namespace App\Commands\Apache;

use LaravelZero\Framework\Commands\Command;

class StartCommand extends Command
{
    const COMMAND = 'apache:start';

    /**
     * @var string
     */
    protected $signature = self::COMMAND;

    /**
     * @var string
     */
    protected $description = 'Start Apache service';

    /**
     * @var \App\Components\Site\Apache
     */
    private $apache;

    /**
     * @param \App\Components\Site\Apache $apache
     */
    public function __construct(
        \App\Components\Site\Apache $apache
    ) {
        $this->apache = $apache;
        parent::__construct();
    }

    /**
     * @return void
     */
    public function handle(): void
    {
        $this->job('Apache Start', function () {
            $this->apache->start();
        });
    }
}
