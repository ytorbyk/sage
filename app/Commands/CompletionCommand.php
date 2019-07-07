<?php

declare(strict_types = 1);

namespace App\Commands;

use App\Command;
use App\Facades\Brew;
use App\Facades\File;
use App\Facades\Stub;

class CompletionCommand extends Command
{
    const COMMAND = 'env:completion';

    /**
     * @var string
     */
    protected $signature = self::COMMAND
        . ' {shell=bash : Supported shells: bash}';

    /**
     * @var string
     */
    protected $description = 'Install and configure completion for the application';

    /**
     * @return void
     */
    public function handle(): void
    {
        $shell = $this->argument('shell');
        switch ($shell) {
            case 'bash':
                $this->setupForBash();
                break;
            default:
                $this->error(sprintf('Wrong shell, currently supported: bash'));
                return;
        }

        $this->output->success('Restart all open terminal windows and completion is ready to use.');
    }

    /**
     * @return void
     */
    private function setupForBash(): void
    {
        $this->task(sprintf('Ensure [%s] installed', config('env.completion.formula')), function () {
            Brew::ensureInstalled((string)config('env.completion.formula'));
        });

        $this->task('Copy completion script', function () {
            File::copy(
                Stub::getPath('completion/bash'),
                (string)config('env.completion.brew_config_completion_path')
            );
        });

        $this->task('Include Brew completion to Bash', function () {

            $sourceText = (string)config('env.completion.brew_completion');
            $bashrcPath = (string)config('env.completion.bashrc_path');
            $bashProfilePath = (string)config('env.completion.bash_profile_path');

            if ((!File::exists($bashrcPath) || strpos(File::get($bashrcPath), $sourceText) === false)
                && (!File::exists($bashProfilePath) || strpos(File::get($bashProfilePath), $sourceText) === false)
            ) {
                File::append($bashProfilePath, PHP_EOL . $sourceText . PHP_EOL);
            }
        });
    }
}
