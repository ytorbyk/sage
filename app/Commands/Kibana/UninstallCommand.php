<?php

declare(strict_types = 1);

namespace App\Commands\Kibana;

use App\Command;
use App\Facades\Brew;
use App\Facades\ApacheHelper;

class UninstallCommand extends Command
{
    const COMMAND = 'kibana:uninstall';

    /**
     * @var string
     */
    protected $signature = self::COMMAND;

    /**
     * @var string
     */
    protected $description = 'Uninstall Kibana';

    /**
     * @return void
     */
    public function handle(): void
    {
        $this->info('Uninstall RabbitMq:');

        if (Brew::isInstalled((string)config('env.kibana.formula'))) {
            $this->call(StopCommand::COMMAND);
            $this->uninstallFormula((string)config('env.kibana.formula'));
        }

        ApacheHelper::deleteVHost((string)config('env.kibana.domain'));
    }
}
