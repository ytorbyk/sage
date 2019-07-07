<?php

declare(strict_types = 1);

namespace App\Commands\Database;

use App\Command;
use App\Facades\Cli;

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
     * @return void
     */
    public function handle(): void
    {
        $name = $this->argument('name');

        if ($this->option('force')) {
            $this->call(DropCommand::COMMAND, ['name' => $name]);
        }

        $this->task(sprintf('Create DB %s if not exists', $name), function () use ($name) {
            Cli::run(sprintf("mysql -e 'CREATE DATABASE IF NOT EXISTS `%s`'", $name));
        });
    }
}
