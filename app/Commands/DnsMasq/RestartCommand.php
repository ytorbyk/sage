<?php

declare(strict_types = 1);

namespace App\Commands\DnsMasq;

use App\Command;

class RestartCommand extends Command
{
    const COMMAND = 'dns:restart';

    /**
     * @var string
     */
    protected $signature = self::COMMAND;

    /**
     * @var string
     */
    protected $description = 'Restart DnsMasq service';

    /**
     * @return void
     */
    public function handle(): void
    {
        $this->info('DnsMasq restart:');

        $this->call(StopCommand::COMMAND);
        $this->call(StartCommand::COMMAND);
    }
}
