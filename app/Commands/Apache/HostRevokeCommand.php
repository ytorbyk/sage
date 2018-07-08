<?php

namespace App\Commands\Apache;

use LaravelZero\Framework\Commands\Command;
use App\Commands\Secure\RevokeCommand;

class HostRevokeCommand extends Command
{
    const COMMAND = 'apache:host-revoke';

    /**
     * @var string
     */
    protected $signature = self::COMMAND . ' {domain}';

    /**
     * @var string
     */
    protected $description = 'Delete Apache Virtual Host';

    /**
     * @var \App\Components\Site\Apache
     */
    private $apache;

    /**
     * @var \App\Components\Site\Secure
     */
    private $siteSecure;

    /**
     * @param \App\Components\Site\Apache $apache
     * @param \App\Components\Site\Secure $siteSecure
     */
    public function __construct(
        \App\Components\Site\Apache $apache,
        \App\Components\Site\Secure $siteSecure
    ) {
        $this->apache = $apache;
        $this->siteSecure = $siteSecure;
        parent::__construct();
    }

    /**
     * @return void
     */
    public function handle(): void
    {
        $domain = $this->argument('domain');

        $this->call(RevokeCommand::COMMAND, ['domain' => $domain]);

        $this->job('Delete Apache Virtual Host', function () use ($domain) {
            $this->apache->deleteVHost($domain);
        });
    }
}
