<?php

namespace App\Commands\DnsMasq;

use LaravelZero\Framework\Commands\Command;

class InstallCommand extends Command
{
    const COMMAND = 'dns:install';

    /**
     * @var string
     */
    protected $signature = self::COMMAND;

    /**
     * @var string
     */
    protected $description = 'Install and configure DnsMasq';

    /**
     * @var \App\Components\Brew
     */
    private $brew;

    /**
     * @var \App\Components\DnsMasq
     */
    private $dnsMasq;

    /**
     * @param \App\Components\Brew $brew
     * @param \App\Components\DnsMasq $dnsMasq
     */
    public function __construct(
        \App\Components\Brew $brew,
        \App\Components\DnsMasq $dnsMasq
    ) {
        $this->brew = $brew;
        $this->dnsMasq = $dnsMasq;
        parent::__construct();
    }

    /**
     * @return void
     */
    public function handle(): void
    {
        $this->info('Install DnsMasq:');

        $needInstall = $this->job('Need to be installed?', function () {
            return !$this->brew->isInstalled(config('env.dns.formula')) ?: 'Installed. Skip';
        });
        if ($needInstall === true) {
            $this->job(sprintf('Install [%s] Brew formula', config('env.dns.formula')), function () {
                $this->brew->install(config('env.dns.formula'));
            });
        }

        $this->job('Configure DnsMasq', function () {
            $this->dnsMasq->configure();
        });

        if ($needInstall) {
            $this->call(StartCommand::COMMAND);
        } else {
            $this->call(RestartCommand::COMMAND);
        }
    }
}
