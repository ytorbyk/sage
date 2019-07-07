<?php

declare(strict_types = 1);

namespace App\Commands\Redis;

use App\Command;
use App\Facades\Brew;

class UninstallCommand extends Command
{
    const COMMAND = 'redis:uninstall';

    /**
     * @var string
     */
    protected $signature = self::COMMAND;

    /**
     * @var string
     */
    protected $description = 'Uninstall Redis';

    /**
     * @return void
     */
    public function handle(): void
    {
        $this->info('Uninstall Redis:');

        if (Brew::isInstalled((string)config('env.redis.formula'))) {
            $this->call(StopCommand::COMMAND);
            $this->uninstallFormula((string)config('env.redis.formula'));
        }
    }
}
