<?php

declare(strict_types = 1);

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
            try {
                BrewService::start((string)config('env.elasticsearch.formula'));
            } catch (\Exception $e) {
                return $e->getMessage();
            }
        });
    }
}
