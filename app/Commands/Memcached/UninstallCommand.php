<?php

declare(strict_types = 1);

namespace App\Commands\Memcached;

use App\Command;
use App\Facades\Brew;

class UninstallCommand extends Command
{
    const COMMAND = 'memcached:uninstall';

    /**
     * @var string
     */
    protected $signature = self::COMMAND;

    /**
     * @var string
     */
    protected $description = 'Uninstall Memcached';

    /**
     * @return void
     */
    public function handle(): void
    {
        $this->info('Uninstall Redis:');

        if (Brew::isInstalled((string)config('env.memcached.formula'))) {
            $this->call(StopCommand::COMMAND);
            $this->uninstallFormula((string)config('env.memcached.formula'));
        }
    }
}
