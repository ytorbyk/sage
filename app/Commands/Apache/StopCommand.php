<?php

namespace App\Commands\Apache;

use LaravelZero\Framework\Commands\Command;

class StopCommand extends Command
{
    const COMMAND = 'apache:stop';

    /**
     * @var string
     */
    protected $signature = self::COMMAND;

    /**
     * @var string
     */
    protected $description = 'Stop Apache service';

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
        $this->job('Apache Stop', function () {
            $this->apache->stop();
        });
    }
}
