<?php

declare(strict_types = 1);

namespace App\Commands\RabbitMq;

use App\Command;
use App\Facades\Cli;

class QueueListCommand  extends Command
{
    const COMMAND = 'rabbitmq:queue:list';

    /**
     * @var string
     */
    protected $signature = self::COMMAND
        . ' {vhost? : VHost name}';

    /**
     * @var string
     */
    protected $description = 'Display RabbitMQ Queues list';

    /**
     * @return void
     */
    public function handle(): void
    {
        $vhostName = $this->argument('vhost');
        $columns = $vhostName ? 'name' : 'vhost name';
        $vhostFilter = $vhostName ? '-V ' . $vhostName  : '';

        try {
            $this->getOutput()->write(Cli::run(sprintf('rabbitmqadmin list queues %s %s', $columns, $vhostFilter)));
        } catch (\Symfony\Component\Process\Exception\ProcessFailedException $e) {
            $this->error(trim($e->getProcess()->getErrorOutput()));
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
    }
}
