<?php

namespace App\Components;

use Symfony\Component\Process\Exception\ProcessFailedException;

class Brew
{
    /**
     * @var \App\Components\CommandLine
     */
    private $cli;

    /**
     * @param \App\Components\CommandLine $cli
     */
    public function __construct(
        \App\Components\CommandLine $cli
    ) {
        $this->cli = $cli;
    }

    /**
     * @return bool
     */
    public function isBrewAvailable()
    {
        return strpos($this->cli->runQuietly('brew -v | grep Homebrew'), 'Homebrew') !== false;
    }

    /**
     * @param string $formula
     * @return string
     */
    public function link(string $formula): string
    {
        return $this->cli->run('brew link ' . $formula . ' --force --overwrite');
    }

    /**
     * @param string $formula
     * @return string
     */
    public function unlink(string $formula): string
    {
        return $this->cli->run('brew unlink ' . $formula . ' --force');
    }

    /**
     * @param string $formula
     * @return bool
     */
    public function isInstalled(string $formula): bool
    {
        return in_array($formula, explode(PHP_EOL, $this->cli->runQuietly('brew list | grep ' . $formula)));
    }

    /**
     * @param string $formula
     * @param array $options
     * @param array $taps
     * @return bool
     *
     * @throws \DomainException
     */
    public function ensureInstalled(string $formula, array $options = [], array $taps = []): bool
    {
        if ($this->isInstalled($formula)) {
            return false;
        }

        $this->install($formula, $options, $taps);
        return true;
    }

    /**
     * @param string $formula
     * @param string[] $options
     * @param string[] $taps
     * @return string
     *
     * @throws \DomainException
     */
    public function install(string $formula, array $options = [], array $taps = []): string
    {
        if (count($taps) > 0) {
            $this->tap($taps);
        }

        try {
            return $this->cli->run('brew install ' . $formula . ' ' . implode(' ', $options));
        } catch (ProcessFailedException $e) {
            throw new \DomainException('Brew was unable to install [' . $formula . '].', 0, $e);
        }
    }

    /**
     * @param string $formula
     * @param string[] $options
     * @return bool
     */
    public function ensureUninstalled(string $formula, array $options = []): bool
    {
        if ($this->isInstalled($formula)) {
            return false;
        }

        $this->uninstall($formula, $options);
        return true;
    }

    /**
     * @param string $formula
     * @param string[] $options
     * @return string
     */
    public function uninstall(string $formula, array $options = []): string
    {
        try {
            return $this->cli->run('brew uninstall ' . $formula . ' ' . implode(' ', $options));
        } catch (ProcessFailedException $e) {
            throw new \DomainException('Brew was unable to uninstall [' . $formula . '].', 0, $e);
        }
    }


    /**
     * @param string[] $formulas
     * @return void
     */
    public function tap(string ... $formulas): void
    {
        $formulas = is_array($formulas) ? $formulas : [$formulas];

        foreach ($formulas as $formula) {
            $this->cli->run('brew tap ' . $formula);
        }
    }

    /**
     * @param string[] $formulas
     * @return void
     */
    public function unTap(string ... $formulas): void
    {
        $formulas = is_array($formulas) ? $formulas : [$formulas];

        foreach ($formulas as $formula) {
            $this->cli->run('brew untap ' . $formula);
        }
    }

    /**
     * @param string $formula
     * @return bool
     */
    public function hasTap(string $formula): bool
    {
        return strpos($this->cli->run('brew tap | grep ' . $formula), $formula) !== false;
    }
}
