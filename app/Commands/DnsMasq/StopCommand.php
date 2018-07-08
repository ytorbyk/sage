<?php

namespace App\Commands\DnsMasq;

use LaravelZero\Framework\Commands\Command;

class StopCommand extends Command
{
    const COMMAND = 'dns:stop';

    /**
     * @var string
     */
    protected $signature = self::COMMAND;

    /**
     * @var string
     */
    protected $description = 'Stop DnsMasq service';

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
        $this->job('DnsMasq Start', function () {
            $this->brewService->stop(config('env.dns.formula'));
        });
    }
}
