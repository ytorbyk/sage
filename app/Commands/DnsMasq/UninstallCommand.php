<?php

namespace App\Commands\DnsMasq;

use LaravelZero\Framework\Commands\Command;

class UninstallCommand extends Command
{
    const COMMAND = 'dns:uninstall';

    /**
     * @var string
     */
    protected $signature = self::COMMAND;

    /**
     * @var string
     */
    protected $description = 'Uninstall DnsMasq';

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
        $this->info('Uninstall DnsMasq:');

        $needUninstall = $this->job('Need to be uninstalled?', function () {
            return $this->brew->isInstalled(config('env.dns.formula')) ?: 'Uninstalled. Skip';
        });
        if ($needUninstall === true) {

            $this->call(StopCommand::COMMAND);

            $this->job(sprintf('Uninstall [%s] Brew formula', config('env.dns.formula')), function () {
                $this->brew->uninstall(config('env.dns.formula'));
            });
        }

        $this->job('Delete DnsMasq config', function () {
            $this->dnsMasq->deleteConfig();
        });
    }
}
