<?php

namespace App\Commands;

use LaravelZero\Framework\Commands\Command;

class CompletionCommand extends Command
{
    /**
     * @var string
     */
    protected $signature = 'env:completion {shell=bash : Supported shells: bash}';

    /**
     * @var string
     */
    protected $description = 'Install and configure completion for the application';

    /**
     * @var \App\Components\Brew
     */
    private $brew;

    /**
     * @var \App\Components\Files
     */
    private $files;

    /**
     * @var \App\Components\Stubs
     */
    private $stubs;

    /**
     * @param \App\Components\Brew $brew
     * @param \App\Components\Files $files
     * @param \App\Components\Stubs $stubs
     */
    public function __construct(
        \App\Components\Brew $brew,
        \App\Components\Files $files,
        \App\Components\Stubs $stubs
    ) {
        $this->brew = $brew;
        $this->files = $files;
        $this->stubs = $stubs;
        parent::__construct();
    }

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
                $this->error(sprintf('Wrong shell, currently supported: bash', $shell));
                return;
        }

        $this->output->success('Restart all open terminal windows and completion is ready to use.');
    }

    /**
     * @return void
     */
    private function setupForBash(): void
    {
        $this->job(sprintf('Ensure [%s] installed', config('env.completion.formula')), function () {
            $this->brew->ensureInstalled(config('env.completion.formula'));
        });

        $this->job('Copy completion script', function () {
            $this->files->copy(
                $this->stubs->getPath('completion/bash'),
                config('env.completion.brew_config_completion_path')
            );
        });

        $this->job('Include Brew completion to Bash', function () {

            $sourceText = config('env.completion.brew_completion');
            $bashrcPath = config('env.completion.bashrc_path');
            $bashProfilePath = config('env.completion.bash_profile_path');

            if ((!$this->files->exists($bashrcPath) || strpos($this->files->get($bashrcPath), $sourceText) === false)
                && (!$this->files->exists($bashProfilePath) || strpos($this->files->get($bashProfilePath), $sourceText) === false)
            ) {
                $this->files->append($bashProfilePath, $sourceText . PHP_EOL);
            }
        });
    }
}
