<?php

namespace App\Commands;

use LaravelZero\Framework\Commands\Command;

class SetupCommand extends Command
{
    /**
     * 4 space indentation for array formatting
     */
    private const INDENT = '    ';

    const COMMAND = 'env:setup';

    /**
     * @var string
     */
    protected $signature = self::COMMAND
    . ' {--o|overwrite-config : Overwrite config with default values}';

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
        if (!$this->brew->isBrewAvailable()) {
            $this->info('Brew is not installed, it is required. Run the next command:');
            $this->comment('/usr/bin/ruby -e "$(curl -fsSL https://raw.githubusercontent.com/Homebrew/install/master/install)"');
        }

        $this->task('Ensure home folder exists', function () {
            $this->files->ensureDirExists(config('env.home'));
        });

        if (!$this->files->exists(config('env.home_config')) || $this->option('overwrite-config')) {
            $this->writeConfig(true);
            $this->info('Default config created, adjust it if needed.');
        } else {
            $this->writeConfig();
        }

        $this->comment('Config: ' . config('env.home_config'));
        $this->comment('Run: sage env:install');
    }

    /**
     * @param bool $overwrite
     * @return void
     */
    private function writeConfig(bool $overwrite = false): void
    {
        $config = $this->loadDefaultConfig();
        if (!$overwrite) {
            $config = array_replace_recursive($config, config('env'));
        }

        $this->task('Generate config', function () use ($config) {
            $content = $this->varExportShort($config, 1);
            $this->files->put(
                config('env.home_config'), '<?php' . PHP_EOL . PHP_EOL . 'return ' . $content . ';' . PHP_EOL
            );
        });
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
