<?php

declare(strict_types = 1);

namespace App\Commands\DnsMasq;

use App\Command;
use App\Facades\BrewService;

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
     * @return void
     */
    public function handle(): void
    {
        $this->task('DnsMasq Stop', function () {
            try {
                BrewService::stop((string)config('env.dns.formula'));
            } catch (\Exception $e) {
                return $e->getMessage();
            }
        });
    }
}
