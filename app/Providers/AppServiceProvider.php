<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use LaravelZero\Framework\Commands\Command;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        /*
         * Performs the given Job, outputs and
         * returns the result.
         *
         * @param string $title
         * @param callable|null $task
         *
         * @return bool With the result of the task.
         */
        Command::macro(
            'job',
            function (string $title, $task = null) {
                $this->output->write("$title: <comment>processing...</comment>");

                $result = is_callable($task) ? $task() : $task;
                if (is_string($result) && !empty($result)) {
                    $resultText = "<comment>$result</comment>";
                } elseif ($result === true || $result === null) {
                    $resultText = '<fg=green>✔</>';
                } else {
                    $resultText = '<fg=red>𐄂</>';
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
        );
    }

    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }
}
