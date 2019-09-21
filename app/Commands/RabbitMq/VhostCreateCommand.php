<?php

declare(strict_types = 1);

namespace App\Commands\RabbitMq;

use App\Command;
use App\Facades\Cli;

class VhostCreateCommand  extends Command
{
    const COMMAND = 'rabbitmq:vhost:create';

    /**
     * @var string
     */
    protected $signature = self::COMMAND
        . ' {name : VHost name}'
        . ' {--f|force : Delete if exist}';

    /**
     * @var string
     */
    protected $description = 'Create RabbitMQ VHost';

    /**
     * @return void
     */
    public function handle(): void
    {
        $name = $this->argument('name');

        if ($this->option('force')) {
            $this->call(VhostDeleteCommand::COMMAND, ['name' => $name]);
        }

        $this->task(sprintf('Create RabbitMQ VHost %s', $name), static function () use ($name) {
            try {
                Cli::run(sprintf('rabbitmqadmin declare vhost name=%s', $name));
            } catch (\Symfony\Component\Process\Exception\ProcessFailedException $e) {
                return trim($e->getProcess()->getErrorOutput());
            } catch (\Exception $e) {
                return $e->getMessage();
            }
        });
    }
}
