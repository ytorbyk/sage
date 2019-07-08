<?php

declare(strict_types = 1);

namespace App\Commands\Memcached;

use App\Command;

class InstallCommand extends Command
{
    const COMMAND = 'memcached:install';

    /**
     * @var string
     */
    protected $signature = self::COMMAND;

    /**
     * @var string
     */
    protected $description = 'Install Memcached';

    /**
     * @return void
     */
    public function handle(): void
    {
        $this->info('Install Memcached:');

        $needInstall = $this->installFormula((string)config('env.memcached.formula'));

        if ($needInstall) {
            $this->call(StartCommand::COMMAND);
        } else {
            $this->call(RestartCommand::COMMAND);
        }
    }
}
