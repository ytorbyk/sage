<?php

declare(strict_types = 1);

namespace App\Commands\Kibana;

use App\Command;
use App\Facades\BrewService;

class StopCommand extends Command
{
    const COMMAND = 'kibana:stop';

    /**
     * @var string
     */
    protected $signature = self::COMMAND;

    /**
     * @var string
     */
    protected $description = 'Stop Kibana service';

    /**
     * @return void
     */
    public function handle(): void
    {
        $this->task('Kibana Stop', function () {
            try {
                BrewService::stop((string)config('env.kibana.formula'));
            } catch (\Exception $e) {
                return $e->getMessage();
            }
        });
    }
}
