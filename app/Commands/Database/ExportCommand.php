<?php

namespace App\Commands\Database;

use App\Command;
use App\Facades\File;
use App\Facades\Cli;

class ExportCommand extends Command
{
    const COMMAND = 'db:export';

    /**
     * @var string
     */
    protected $signature = self::COMMAND
        . ' {name? : Database name}'
        . ' {file? : File name}';

    /**
     * @var string
     */
    protected $description = 'Export and Gzip Database';

    /**
     * @return void
     */
    public function handle(): void
    {
        $dbName = $this->argument('name') ?: $this->getDbName();
        if (!$dbName) {
            return;
        }

        $file = $this->argument('file') ?: $this->getDumpName($dbName);

        $dumpPath = $this->getDumpPath($this->updateDumpExtension($file));
        $packCommand = $this->getPackCommand($dumpPath);

        Cli::passthru("mysqldump {$dbName} --routines=true"
            . " | pv -b -t -w 80 -N Export "
            . " | sed -e 's/DEFINER[ ]*=[ ]*[^*]*\*/\*/' | sed -e 's/ROW_FORMAT=FIXED//g' "
            . " {$packCommand} > {$dumpPath}");

        $this->task(sprintf('DB %s exported', $dbName));
        $this->comment(sprintf('Dump path: %s', $dumpPath));
    }

    /**
     * @return string|null
     */
    private function getDbName(): ?string
    {
        $dbs =  Cli::run('mysql -N -e "SHOW DATABASES"');
        $dbs = array_filter(explode(PHP_EOL, $dbs));
        $dbs = array_diff($dbs, ['sys', 'mysql', 'performance_schema', 'information_schema']);
        return $this->askWithCompletion('Enter DB name', $dbs);
    }

    /**
     * @param string $dbName
     * @return string
     */
    private function getDumpName(string $dbName): string
    {
        $defaultName = $dbName . '.' . date('d-m-Y') . '.sql.gz';
        return $this->ask('Enter Dump file name (location)', $defaultName);
    }

    /**
     * @param string $file
     * @return string
     */
    private function updateDumpExtension(string $file): string
    {
        return in_array(File::extension($file), ['sql', 'gz'], true) ? $file : $file . '.sql.gz';
    }

    /**
     * @param string $dumpPath
     * @return string
     */
    private function getPackCommand(string $dumpPath): string
    {
        return File::extension($dumpPath) === 'gz' ? '| gzip' : '';
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
