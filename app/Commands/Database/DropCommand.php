<?php

declare(strict_types = 1);

namespace App\Commands\Database;

use App\Command;
use App\Facades\Cli;

class DropCommand extends Command
{
    const COMMAND = 'db:drop';

    /**
     * @var string
     */
    protected $signature = self::COMMAND . ' {name : Database name}';

    /**
     * @var string
     */
    protected $description = 'Drop Database';

    /**
     * @return void
     */
    public function handle(): void
    {
        $name = $this->argument('name');

        $this->task(sprintf('Drop DB %s if exists', $name), function () use ($name) {
            Cli::run(sprintf("mysql -e 'DROP DATABASE IF EXISTS `%s`'", $name));
        });
    }
}
