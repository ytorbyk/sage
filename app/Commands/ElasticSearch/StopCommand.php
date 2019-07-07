<?php

declare(strict_types = 1);

namespace App\Commands\ElasticSearch;

use App\Command;
use App\Facades\BrewService;

class StopCommand extends Command
{
    const COMMAND = 'elasticsearch:stop';

    /**
     * @var string
     */
    protected $signature = self::COMMAND;

    /**
     * @var string
     */
    protected $description = 'Stop ElasticSearch service';

    /**
     * @return void
     */
    public function handle(): void
    {
        $this->task('ElasticSearch Stop', function () {
            try {
                BrewService::stop((string)config('env.elasticsearch.formula'));
            } catch (\Exception $e) {
                return $e->getMessage();
            }
        });
    }
}
