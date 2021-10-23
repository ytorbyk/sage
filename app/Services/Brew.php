<?php

declare(strict_types = 1);

namespace App\Services;

use Symfony\Component\Process\Exception\ProcessFailedException;
use App\Facades\Cli;

class Brew
{
    /**
     * @return bool
     */
    public function isBrewAvailable(): bool
    {
        return strpos(Cli::runQuietly('brew -v | grep Homebrew'), 'Homebrew') !== false;
    }

    /**
     * @param string $formula
     * @return string
     */
    public function link(string $formula): string
    {
        return Cli::run('brew link ' . $formula . ' --force --overwrite');
    }

    /**
     * @param string $formula
     * @return string
     */
    public function unlink(string $formula): string
    {
        return Cli::run('brew unlink ' . $formula);
    }

    /**
     * @param string $formula
     * @return bool
     */
    public function isInstalled(string $formula): bool
    {
        return in_array($formula, explode(PHP_EOL, Cli::runQuietly('brew list --formula | grep ' . $formula)), true);
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
     * @param array $tap
     * @return string
     *
     * @throws \DomainException
     */
    public function install(string $formula, array $options = [], array $tap = null): string
    {
        $vendor = '';
        if (!empty($tap)) {
            $this->tap(... $tap);
            $vendor = array_shift($tap) . '/';
        }

        try {
            return Cli::run('brew install ' . $vendor . $formula . ' ' . implode(' ', $options));
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
        if (!$this->isInstalled($formula)) {
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
            return Cli::run('brew uninstall ' . $formula . ' ' . implode(' ', $options));
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
            Cli::run('brew tap ' . $formula);
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
            Cli::run('brew untap ' . $formula);
        }
    }

    /**
     * @param string $formula
     * @return bool
     */
    public function hasTap(string $formula): bool
    {
        return strpos(Cli::run('brew tap | grep ' . $formula), $formula) !== false;
    }
}
