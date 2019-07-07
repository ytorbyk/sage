<?php

declare(strict_types = 1);

namespace App\Commands\Apache;

use App\Command;
use App\Facades\BrewService;

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
     * @return void
     */
    public function handle(): void
    {
        $this->task('Apache Start', function () {
            try {
                BrewService::start((string)config('env.apache.formula'), true);
            } catch (\Exception $e) {
                return $e->getMessage();
            }
        });
    }
}
