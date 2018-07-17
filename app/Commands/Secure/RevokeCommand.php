<?php

namespace App\Commands\Secure;

use App\Command;
use App\Facades\Secure;

class RevokeCommand extends Command
{
    const COMMAND = 'secure:revoke';

    /**
     * @var string
     */
    protected $signature = self::COMMAND
        . ' {domain}';

    /**
     * @var string
     */
    protected $description = 'Revoke certificate';

    /**
     * @return void
     */
    public function handle(): void
    {
        $domain = $this->argument('domain');

        $this->task('Delete SSL certificate', function () use ($domain) {
            if (Secure::hasPredefined($domain)) {
                return 'SSL certificate is predefined. Skipping...';
            }

            if (!Secure::canGenerate($domain)) {
                return 'The domain cannot be secured. Skipping...';
            }

            Secure::delete($domain);
        });
    }
}
