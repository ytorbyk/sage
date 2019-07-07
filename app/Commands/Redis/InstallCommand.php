<?php

declare(strict_types = 1);

namespace App\Commands\Redis;

use App\Command;

class InstallCommand extends Command
{
    const COMMAND = 'redis:install';

    /**
     * @var string
     */
    protected $signature = self::COMMAND;

    /**
     * @var string
     */
    protected $description = 'Install Redis';

    /**
     * @return void
     */
    public function handle(): void
    {
        $this->info('Install Redis:');

        $needInstall = $this->installFormula((string)config('env.redis.formula'));

        if ($needInstall) {
            $this->call(StartCommand::COMMAND);
        } else {
            $this->call(RestartCommand::COMMAND);
        }
    }
}
