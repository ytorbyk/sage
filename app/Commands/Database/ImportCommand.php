<?php

namespace App\Commands\Database;

use LaravelZero\Framework\Commands\Command;

class ImportCommand extends Command
{
    const COMMAND = 'db:import';

    /**
     * @var string
     */
    protected $signature = self::COMMAND
        . ' {name? : Database name}'
        . ' {file? : File name}';

    /**
     * @var string
     */
    protected $description = 'Import Database';

    /**
     * @var \App\Components\CommandLine
     */
    private $cli;

    /**
     * @var \App\Components\Files
     */
    private $files;

    /**
     * @var array
     */
    private $fileType = [
        'gz' => 'gunzip -cf',
        'zip' => 'unzip -p',
        'sql' => '',
    ];

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
        $name = $this->argument('name') ?: $this->ask('Enter Db name');
        if (!$name) {
            return;
        }

        $file = $this->argument('file') ?: $this->getDumpName();
        if ($file === null) {
            return;
        }

        $dbPath = $this->getDumpPath($file);
        if (!$this->verifyPath($dbPath)) {
            $this->error(sprintf('Passed path does not exist or not a file: %s', $dbPath));
            return;
        }

        $fileType = $this->files->extension($file);
        if (!isset($this->fileType[$this->files->extension($file)])) {
            $this->error(sprintf('The file type is not supported: %s', $fileType));
            return;
        }

        $this->call(CreateCommand::COMMAND, ['name' => $name, '--force' => true]);

        $dumpPath = $dbPath;

        $tmpFilePath = config('env.tmp_path') . DIRECTORY_SEPARATOR . 'dump.sql';
        if (!empty($this->fileType[$fileType])) {
            $this->files->ensureDirExists(config('env.tmp_path'));
            $this->files->delete($tmpFilePath);

            $this->cli->passthru("{$this->fileType[$fileType]} {$dbPath} | pv -b -t -w 80 -N Unpack  > {$tmpFilePath}");
            $dumpPath = $tmpFilePath;
        }

        $this->cli->passthru("pv {$dumpPath} -w 80 -N Import | sed -e 's/DEFINER[ ]*=[ ]*[^*]*\*/\*/' | sed -e 's/ROW_FORMAT=FIXED//g' | mysql --force {$name}");

        $this->files->delete($tmpFilePath);
        $this->job('Imported!');
    }

    /**
     * @return string|null
     */
    private function getDumpName(): ?string
    {
        $dumps = $this->getDumpList();

        $options = array_map(function ($dump) {
            return sprintf('%-50s %-15s %s', $dump['name'], $dump['size'], $dump['date']);
        }, $dumps);

        return $this->menu('Import DB', $options)
            ->setForegroundColour('green')
            ->setBackgroundColour('black')
            ->setExitButtonText('Cancel')
            ->addLineBreak('-')
            ->open();
    }

    /**
     * @return string[]
     */
    private function getDumpList(): array
    {
        /** @var \Symfony\Component\Finder\SplFileInfo[] $files */
        $files = $this->files->files(config('env.db.dump_path'));

        $dumps = [];
        foreach ($files as $file) {
            if (!$file->isFile() || !isset($this->fileType[$file->getExtension()])) {
                continue;
            }

            $dumps[$file->getFilename()] = [
                'name' => $file->getFilename(),
                'size' => $this->files->getFormatedFileSize($file->getSize()),
                'date' => date('d M Y', $file->getCTime()),
            ];
        }
        return $dumps;
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


    /**
     * @param string $dbPath
     * @return bool
     */
    private function verifyPath(string $dbPath): bool
    {
        return $this->files->exists($dbPath) && $this->files->isFile($dbPath);
    }
}
