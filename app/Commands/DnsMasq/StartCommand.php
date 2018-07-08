<?php

namespace App\Commands\DnsMasq;

use LaravelZero\Framework\Commands\Command;

class StartCommand extends Command
{
    const COMMAND = 'dns:start';

    /**
     * @var string
     */
    protected $signature = self::COMMAND;

    /**
     * @var string
     */
    protected $description = 'Start DnsMasq service';

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
            $this->brewService->start(config('env.dns.formula'), true);
        });
    }
}
