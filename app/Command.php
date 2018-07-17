<?php

namespace App;

/**
 * @method bool installFormula(string $formula)
 * @method bool uninstallFormula(string $formula)
 */
class Command extends \LaravelZero\Framework\Commands\Command
{
    /**
     * Write a string as success output.
     *
     * @param  string  $string
     * @param  null|int|string  $verbosity
     * @return void
     */
    public function success($string, $verbosity = null)
    {
        $this->output->writeln($this->successText($string), $this->parseVerbosity($verbosity));
    }

    /**
     * @param string $string
     * @return string
     */
    protected function successText(string $string): string
    {
        return "<fg=green>$string</>";
    }

    /**
     * Write a string as error output.
     *
     * @param  string  $string
     * @param  null|int|string  $verbosity
     * @return void
     */
    public function error($string, $verbosity = null)
    {
        $this->output->writeln($this->errorText($string), $this->parseVerbosity($verbosity));
    }

    /**
     * @param string $string
     * @return string
     */
    protected function errorText(string $string): string
    {
        return "<fg=red>$string</>";
    }

    /**
     * @param string $title
     * @param null $task
     * @return null
     */
    public function task(string $title, $task = null)
    {
        $this->output->write("$title: <comment>processing...</comment>");

        $result = is_callable($task) ? $task() : $task;

        if (is_string($result) && !empty($result)) {
            $resultText = "<comment>$result</comment>";
        } elseif ($result === true || $result === null) {
            $resultText = $this->successText('✔');
        } else {
            $resultText = $this->errorText('𐄂');
        }

        if ($this->output->isDecorated()) { // Determines if we can use escape sequences
            // Move the cursor to the beginning of the line
            $this->output->write("\x0D");

            // Erase the line
            $this->output->write("\x1B[2K");
        } else {
            $this->output->writeln(''); // Make sure we first close the previous line
        }

        $this->output->writeln("$title: " . $resultText);

        return $result;
    }
}
