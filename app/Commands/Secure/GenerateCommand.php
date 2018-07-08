<?php

namespace App\Commands\Secure;

use LaravelZero\Framework\Commands\Command;

class GenerateCommand extends Command
{
    const COMMAND = 'secure:generate';

    /**
     * @var string
     */
    protected $signature = self::COMMAND . ' {domain} {aliases?*}';

    /**
     * @var string
     */
    protected $description = 'Generate certificate';

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
        $aliases = $this->argument('aliases');

        $this->job('Generate SSL certificate', function () use ($domain, $aliases) {
            if (!$this->siteSecure->canSecure($domain)) {
                return '<fg=red>The domain cannot be secured</>';
            }

            if ($this->siteSecure->canGenerate($domain)) {
                $this->siteSecure->delete($domain);
                $this->siteSecure->generate($domain, $aliases);
                return true;
            }

            return 'SSL certificate is predefined. Skipping...';
        });
    }
}
