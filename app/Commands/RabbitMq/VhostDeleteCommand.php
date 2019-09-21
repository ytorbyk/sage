<?php

declare(strict_types = 1);

namespace App\Commands\RabbitMq;

use App\Command;
use App\Facades\Cli;

class VhostDeleteCommand  extends Command
{
    const COMMAND = 'rabbitmq:vhost:delete';

    /**
     * @var string
     */
    protected $signature = self::COMMAND
        . ' {name : VHost name}';

    /**
     * @var string
     */
    protected $description = 'Delete RabbitMQ VHost';

    /**
     * @return void
     */
    public function handle(): void
    {
        $name = $this->argument('name');

        $this->task(sprintf('Delete RabbitMQ VHost %s', $name), static function () use ($name) {
            try {
                Cli::run(sprintf('rabbitmqadmin delete vhost name=%s', $name));
            } catch (\Symfony\Component\Process\Exception\ProcessFailedException $e) {
                return trim($e->getProcess()->getErrorOutput());
            } catch (\Exception $e) {
                return $e->getMessage();
            }
        });
    }
}
