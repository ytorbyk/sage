<?php

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
        $this->task('ElasticSearch Start', function () {
            BrewService::stop(config('env.elasticsearch.formula'));
        });
    }
}
