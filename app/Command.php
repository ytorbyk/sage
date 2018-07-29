<?php

namespace App;

use PhpSchool\CliMenu\CliMenu;
use PhpSchool\CliMenu\Builder\CliMenuBuilder;
use NunoMaduro\LaravelConsoleMenu\MenuOption;
use PhpSchool\CliMenu\Action\GoBackAction;

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

    /**
     * @param string $title
     * @param array $options
     * @return string|null
     */
    public function menu(string $title = '', array $options = [])
    {
        $addMenuOption = function (CliMenuBuilder $menuBuilder, array $options, &$optionSelected) use (&$addMenuOption) : void
        {
            foreach ($options as $value => $label) {
                if (is_array($label)) {
                    $menuBuilder->addSubMenu($value, function (CliMenuBuilder $subMenu) use ($value, $label, &$optionSelected, &$addMenuOption) {
                        $subMenu->setTitle($value);
                        $subMenu->disableDefaultItems();

                        $addMenuOption($subMenu, $label, $optionSelected);

                        $subMenu->addLineBreak('-');
                        $subMenu->addItem('Go Back', new GoBackAction);
                    });
                } else {
                    $menuBuilder->addMenuItem(
                        new MenuOption(
                            $value, $label, function (CliMenu $menu) use (&$optionSelected) {
                            $optionSelected = $menu->getSelectedItem();
                            $menu->close();
                        })
                    );
                }
            }
        };

        $menuBuilder = new CliMenuBuilder;
        $menuBuilder->setTitle($title);
        $menuBuilder->setTitleSeparator('=');
        $menuBuilder->setForegroundColour('green');
        $menuBuilder->setBackgroundColour('black');

        $optionSelected = null;
        $addMenuOption($menuBuilder, $options, $optionSelected);

        $menuBuilder->addLineBreak('-');
        $menuBuilder->setExitButtonText('Cancel');
        $menuBuilder->build()->open();

        return $optionSelected instanceof MenuOption ? $optionSelected->getValue() : null;
    }
}
