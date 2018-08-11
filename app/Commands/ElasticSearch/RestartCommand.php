<?php

namespace App\Commands\ElasticSearch;

use App\Command;

class RestartCommand extends Command
{
    const COMMAND = 'elasticsearch:restart';

    /**
     * @var string
     */
    protected $signature = self::COMMAND;

    /**
     * @var string
     */
    protected $description = 'Restart ElasticSearch service';

    /**
     * @return void
     */
    public function handle(): void
    {
        $this->info('ElasticSearch restart:');

        $this->call(StopCommand::COMMAND);
        $this->call(StartCommand::COMMAND);
    }
}
