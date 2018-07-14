<?php

namespace App\Commands\Database;

use LaravelZero\Framework\Commands\Command;

class ExportCommand extends Command
{
    const COMMAND = 'db:export';

    /**
     * @var string
     */
    protected $signature = self::COMMAND
        . ' {name : Database name}'
        . ' {file? : File name}';

    /**
     * @var string
     */
    protected $description = 'Export and Gzip Database';

    /**
     * @var \App\Components\CommandLine
     */
    private $cli;

    /**
     * @var \App\Components\Files
     */
    private $files;

    /**
     * @param \App\Components\CommandLine $cli
     * @param \App\Components\Files $files
     */
    public function __construct(
        \App\Components\CommandLine $cli,
        \App\Components\Files $files
    ) {
        $this->cli = $cli;
        $this->files = $files;
        parent::__construct();
    }

    /**
     * @return void
     */
    public function handle(): void
    {
        $dbName = $this->argument('name');
        $file = $this->argument('file') ?: $dbName;

        $dumpPath = $this->getDumpPath($this->getDumpName($file));
        $packCommand = $this->getPackCommand($dumpPath);

        $this->cli->passthru("mysqldump {$dbName} "
            . "| pv -b -t -w 80 -N Export "
            . "| sed -e 's/DEFINER[ ]*=[ ]*[^*]*\*/\*/' | sed -e 's/ROW_FORMAT=FIXED//g' "
            . " {$packCommand} > {$dumpPath}");

        $this->job(sprintf('DB %s exported', $dbName));
        $this->comment(sprintf('Dump path: %s', $dumpPath));
    }

    /**
     * @param string $dumpPath
     * @return bool
     */
    private function getPackCommand(string $dumpPath): bool
    {
        return $this->files->extension($dumpPath) === 'gz' ? '| gzip' : '';
    }

    /**
     * @param string $file
     * @return string
     */
    private function getDumpName(string $file): string
    {
        return in_array($this->files->extension($file), ['sql', 'gz'], true) ? $file : $file . '.sql.gz';
    }

    /**
     * @param string $file
     * @return string
     */
    private function getDumpPath(string $file): string
    {
        if (strpos($file, '/') === 0) {
            $dbPath = $file;
        } elseif (strpos($file, './') === 0) {
            $dbPath = getcwd() . DIRECTORY_SEPARATOR . substr($file, 2);
        } else {
            $dbPath = config('env.db.dump_path') . DIRECTORY_SEPARATOR . $file;
        }

        return $dbPath;
    }
}
