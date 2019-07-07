<?php

declare(strict_types = 1);

namespace App\Services;

use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class CommandLine
{
    /**
     * @param string $command
     * @return string
     *
     * @throws ProcessFailedException
     */
    public function run(string $command): string
    {
        $process = $this->runCommand($command);

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        return $process->getOutput();
    }

    /**
     * @param string $command
     * @return string
     */
    public function runQuietly(string $command): string
    {
        $process = $this->runCommand($command);
        return $process->isSuccessful() ? $process->getOutput() : '';
    }

    /**
     * @param string $command
     * @return Process
     */
    private function runCommand(string $command): Process
    {
        $process = new Process($command, null, null, null, 5400.);
        $process->run();
        return $process;
    }

    /**
     * Pass the command to the command line and display the output.
     *
     * @param  string  $command
     * @return void
     */
    public function passthru(string $command): void
    {
        passthru($command);
    }

}
