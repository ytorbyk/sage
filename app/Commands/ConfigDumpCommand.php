<?php

namespace App\Commands;

use App\Command;
use App\Facades\File;
use App\Facades\Stub;

class ConfigDumpCommand extends Command
{
    /**
     * 4 space indentation for array formatting
     */
    private const INDENT = '    ';

    const COMMAND = 'env:config-dump';

    /**
     * @var string
     */
    protected $signature = self::COMMAND
        . ' {--s|skip-custom : Skip custom values}';

    /**
     * @var string
     */
    protected $description = 'Dump config values';

    /**
     * @return void
     */
    public function handle(): void
    {
        $this->task('Ensure public home folder exists', function () {
            File::ensureDirExists(config('env.home_public'));
        });

        $this->writeConfig($this->option('skip-custom'));

        $this->comment('Config dump: ' . $this->getConfigDumpPath());
        $this->info('The config will not be used.');
        $this->info('It could be customized and moved to: ' . config('env.config_path'));
        $this->info('There should be changed values only, they will be merged with default values.');
    }

    /**
     * @return string
     */
    private function getConfigDumpPath(): string
    {
        return config('env.home_public') . DIRECTORY_SEPARATOR . 'config.php';
    }

    /**
     * @param bool $skipCustom
     * @return void
     */
    private function writeConfig(bool $skipCustom = false): void
    {
        $config = $skipCustom ? $this->loadDefaultConfig() : config('env');

        $this->task('Generate config', function () use ($config) {
            $content = $this->varExportShort($config, 1);
            File::put(
                $this->getConfigDumpPath(), '<?php' . PHP_EOL . PHP_EOL . 'return ' . $content . ';' . PHP_EOL
            );
        });
    }

    /**
     * @return array
     */
    private function loadDefaultConfig(): array
    {
        return include Stub::getPath('config/env.php');
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
