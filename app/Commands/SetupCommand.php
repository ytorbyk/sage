<?php

namespace App\Commands;

use LaravelZero\Framework\Commands\Command;

class SetupCommand extends Command
{
    /**
     * 4 space indentation for array formatting
     */
    private const INDENT = '    ';

    /**
     * @var string
     */
    protected $signature = 'env:setup';

    /**
     * @var string
     */
    protected $description = 'Setup home folder and config';

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
     * @var \App\Components\CommandLine
     */
    private $cli;

    /**
     * @param \App\Components\Brew $brew
     * @param \App\Components\Files $files
     * @param \App\Components\Stubs $stubs
     * @param \App\Components\CommandLine $cli
     */
    public function __construct(
        \App\Components\Brew $brew,
        \App\Components\Files $files,
        \App\Components\Stubs $stubs,
        \App\Components\CommandLine $cli
    ) {
        $this->brew = $brew;
        $this->files = $files;
        $this->stubs = $stubs;
        $this->cli = $cli;
        parent::__construct();
    }

    /**
     * @return void
     */
    public function handle(): void
    {
        if (!$this->files->exists(config('env.home_config')) || $this->confirm('Config already exists, do you want to overwrite?')) {
            $this->writeCOnfig();
        }
    }

    /**
     * @return void
     */
    private function writeCOnfig(): void
    {
        if (!$this->brew->isBrewAvailable()) {
            $this->info('Brew is not installed, it is required. Run the next command:');
            $this->comment('/usr/bin/ruby -e "$(curl -fsSL https://raw.githubusercontent.com/Homebrew/install/master/install)"');
        }

        $this->task('Ensure home folder exists', function () {
            $this->files->ensureDirExists(config('env.home'));
        });

        $this->task('Create default config', function () {
            $content = $this->varExportShort($this->loadDefaultConfig(), 1);
            $this->files->put(
                config('env.home_config'), '<?php' . PHP_EOL . PHP_EOL . 'return ' . $content . ';' . PHP_EOL
            );
        });

        $this->cli->run('open ' . config('env.home_config'));
        $this->notify(
            config('app.name'),
            'Default config created, adjust it if needed.' . PHP_EOL .
            'Run sage env:install'
        );

        $this->info('Default config created, adjust it if needed.');
        $this->comment('Config: ' . config('env.home_config'));
        $this->info('Run `sage env:install`');
    }

    /**
     * @return array
     */
    private function loadDefaultConfig(): array
    {
        return include $this->stubs->getPath('config.php');
    }

    /**
     * @param mixed $var
     * @param int $depth
     * @return string
     */
    private function varExportShort($var, int $depth = 0): string
    {
        if (!is_array($var)) {
            return var_export($var, true);
        }

        $indexed = array_keys($var) === range(0, count($var) - 1);
        $expanded = [];
        foreach ($var as $key => $value) {
            $expanded[] = str_repeat(self::INDENT, $depth)
                . ($indexed ? '' : $this->varExportShort($key) . ' => ')
                . $this->varExportShort($value, $depth + 1);
        }

        return sprintf("[\n%s\n%s]", implode(",\n", $expanded), str_repeat(self::INDENT, $depth - 1));
    }
}
