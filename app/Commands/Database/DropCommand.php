<?php

namespace App\Commands\Database;

use LaravelZero\Framework\Commands\Command;

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
    protected $description = 'Drop MySQL Database';

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
        $name = $this->argument('name');

        $this->job(sprintf('Drop DB %s if exists', $name), function () use ($name) {
            $this->cli->run(sprintf("mysql -e 'DROP DATABASE IF EXISTS `%s`'", $name));
        });
    }
}
