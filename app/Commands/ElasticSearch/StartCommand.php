<?php

namespace App\Commands\ElasticSearch;

use App\Command;
use App\Facades\BrewService;

class StartCommand extends Command
{
    const COMMAND = 'elasticsearch:start';

    /**
     * @var string
     */
    protected $signature = self::COMMAND;

    /**
     * @var string
     */
    protected $description = 'Start ElasticSearch service';

    /**
     * @return void
     */
    public function handle(): void
    {
        $this->task('ElasticSearch Start', function () {
            BrewService::start(config('env.elasticsearch.formula'));
        });
    }
}
