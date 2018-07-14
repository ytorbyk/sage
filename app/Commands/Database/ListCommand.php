<?php

namespace App\Commands\Database;

use LaravelZero\Framework\Commands\Command;

class ListCommand extends Command
{
    const COMMAND = 'db:list';

    /**
     * @var string
     */
    protected $signature = self::COMMAND
    . ' {query? : Filter the list}';

    /**
     * @var string
     */
    protected $description = 'Display DB list (simple wrapper for mysqlshow)';

    /**
     * @var \App\Components\CommandLine
     */
    private $cli;

    /**
     * @param \App\Components\CommandLine $cli
     */
    public function __construct(
        \App\Components\CommandLine $cli
    ) {
        $this->cli = $cli;
        parent::__construct();
    }

    /**
     * @return void
     */
    public function handle(): void
    {
        $query = $this->argument('query');
        $query = $query ? '%' . $query . '%' : '';
        $this->cli->passthru('mysqlshow ' . $query);
    }
}
