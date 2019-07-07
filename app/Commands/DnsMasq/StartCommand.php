<?php

declare(strict_types = 1);

namespace App\Commands\DnsMasq;

use App\Command;
use App\Facades\BrewService;

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
     * @return void
     */
    public function handle(): void
    {
        $this->task('DnsMasq Start', function () {
            try {
                BrewService::start((string)config('env.dns.formula'), true);
            } catch (\Exception $e) {
                return $e->getMessage();
            }
        });
    }
}
