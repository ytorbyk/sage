<?php

declare(strict_types = 1);

namespace App\Commands\Apache;

use App\Command;
use App\Facades\BrewService;

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
     * @return void
     */
    public function handle(): void
    {
        $this->task('Apache Stop', function () {
            try {
                BrewService::stop((string)config('env.apache.formula'));
            } catch (\Exception $e) {
                return $e->getMessage();
            }
        });
    }
}
