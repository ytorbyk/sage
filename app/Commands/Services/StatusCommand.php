<?php

declare(strict_types = 1);

namespace App\Commands\Services;

use App\Command;
use App\Facades\BrewService;

class StatusCommand extends Command
{
    const COMMAND = 'services:status';

    /**
     * @var string
     */
    protected $signature = self::COMMAND;

    /**
     * @var string
     */
    protected $description = 'Display services statuses';

    /**
     * @return void
     */
    public function handle(): void
    {
        $services = config('env.services');
        $maxLength = max(array_map('strlen', $services)) + 1;

        $this->info('Services:');
        foreach ($services as $service) {
            $isRunning = BrewService::isStarted((string)config(sprintf('env.%s.formula', $service)));
            $status = $isRunning ? $this->successText('running') : $this->errorText('stopped');

            $this->comment(sprintf("%-{$maxLength}s %s", $service . ':', $status));
        }
    }
}
