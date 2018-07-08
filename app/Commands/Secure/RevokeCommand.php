<?php

namespace App\Commands\Secure;

use LaravelZero\Framework\Commands\Command;

class RevokeCommand extends Command
{
    const COMMAND = 'secure:revoke';

    /**
     * @var string
     */
    protected $signature = self::COMMAND . ' {domain}';

    /**
     * @var string
     */
    protected $description = 'Revoke certificate';

    /**
     * @var \App\Components\Site\Secure
     */
    private $siteSecure;

    /**
     * @param \App\Components\Site\Secure $siteSecure
     */
    public function __construct(
        \App\Components\Site\Secure $siteSecure
    ) {
        $this->siteSecure = $siteSecure;
        parent::__construct();
    }

    /**
     * @return void
     */
    public function handle(): void
    {
        $domain = $this->argument('domain');

        $this->job('Delete SSL certificate', function () use ($domain) {
            if ($this->siteSecure->hasPredefined($domain)) {
                return 'SSL certificate is predefined. Skipping...';
            }

            if (!$this->siteSecure->canGenerate($domain)) {
                return 'The domain cannot be secured. Skipping...';
            }

            $this->siteSecure->delete($domain);
        });
    }
}
