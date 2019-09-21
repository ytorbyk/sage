<?php

declare(strict_types = 1);

namespace App\Commands\RabbitMq;

use App\Command;
use App\Facades\Cli;

class VhostListCommand  extends Command
{
    const COMMAND = 'rabbitmq:vhost:list';

    /**
     * @var string
     */
    protected $signature = self::COMMAND;

    /**
     * @var string
     */
    protected $description = 'Display RabbitMQ VHost list';

    /**
     * @return void
     */
    public function handle(): void
    {
        try {
            $this->getOutput()->write(Cli::run('rabbitmqadmin list vhosts name'));
        } catch (\Symfony\Component\Process\Exception\ProcessFailedException $e) {
            $this->error(trim($e->getProcess()->getErrorOutput()));
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
    }
}
