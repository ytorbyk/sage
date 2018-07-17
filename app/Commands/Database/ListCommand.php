<?php

namespace App\Commands\Database;

use App\Command;
use App\Facades\Cli;

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
     * @return void
     */
    public function handle(): void
    {
        $query = $this->argument('query');
        $query = $query ? '%' . $query . '%' : '';
        Cli::passthru('mysqlshow ' . $query);
    }
}
