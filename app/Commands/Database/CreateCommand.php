<?php

namespace App\Commands\Database;

use LaravelZero\Framework\Commands\Command;

class CreateCommand extends Command
{
    const COMMAND = 'db:create';

    /**
     * @var string
     */
    protected $signature = self::COMMAND . ' {name : Database name} {--f|force : Delete if exist}';

    /**
     * @var string
     */
    protected $description = 'Create Database';

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

        if ($this->option('force')) {
            $this->call(DropCommand::COMMAND, ['name' => $name]);
        }

        $this->job(sprintf('Create DB %s if not exists', $name), function () use ($name) {
            $this->cli->run(sprintf("mysql -e 'CREATE DATABASE IF NOT EXISTS `%s`'", $name));
        });
    }
}
