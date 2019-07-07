<?php

declare(strict_types = 1);

namespace App\Commands\Secure;

use App\Command;
use App\Facades\Secure;

class GenerateCommand extends Command
{
    const COMMAND = 'secure:generate';

    /**
     * @var string
     */
    protected $signature = self::COMMAND
        . ' {domain} {aliases?*}';

    /**
     * @var string
     */
    protected $description = 'Generate certificate';

    /**
     * @return void
     */
    public function handle(): void
    {
        $domain = $this->argument('domain');
        $aliases = $this->argument('aliases');

        $this->task('Generate SSL certificate', function () use ($domain, $aliases) {
            if (!Secure::canSecure($domain)) {
                return $this->errorText('The domain cannot be secured');
            }

            if (Secure::canGenerate($domain)) {
                Secure::delete($domain);
                Secure::generate($domain, $aliases);
                return true;
            }

            return 'SSL certificate is predefined. Skipping...';
        });
    }
}
