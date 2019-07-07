<?php

declare(strict_types = 1);

namespace App\Services;

use App\Facades\Cli;
use App\Facades\Brew;

class BrewService
{
    protected const SERVICE_STARTED = 'started';
    protected const SERVICE_ROOT = 'root';

    /**
     * @param string $service
     * @return bool
     *
     * @throws \DomainException
     */
    public function isStarted(string $service): bool
    {
        return $this->getServiceData($service)[self::SERVICE_STARTED];
    }

    /**
     * @return bool[]
     */
    public function getServicesStatus(): array
    {
        return array_map(function ($serviceData) {
            return $serviceData[self::SERVICE_STARTED] ?? false;
        }, $this->getServices());
    }

    /**
     * @param string $service
     * @return array
     *
     * @throws \DomainException
     */
    private function getServiceData(string $service): array
    {
        if (!Brew::isInstalled($service)) {
            throw new \DomainException("[{$service}] formula is not installed");
        }

        $services = $this->getServices();
        if (!isset($services[$service])) {
            throw new \DomainException("[{$service}] formula is not a service");
        }

        return $services[$service];
    }

    /**
     * $result['serviceName'] = ['started' => bool, 'root' => bool]
     *
     * @return array
     */
    private function getServices(): array
    {
        $services = [];

        $serviceLines = explode(PHP_EOL, Cli::run('brew services list'));
        array_shift($serviceLines);
        foreach ($serviceLines as $serviceLine) {
            $serviceLine = array_values(array_filter(explode(' ', $serviceLine)));
            if (empty($serviceLine)) {
                continue;
            }

            $services[$serviceLine[0]] = [
                self::SERVICE_STARTED => ($serviceLine[1] === 'started'),
                self::SERVICE_ROOT => null
            ];

            if ($services[$serviceLine[0]][self::SERVICE_STARTED] === true) {
                $services[$serviceLine[0]][self::SERVICE_ROOT] = ($serviceLine[2] === 'root');
            }
        }

        return $services;
    }

    /**
     * @param string $service
     * @param bool $root
     * @return bool
     *
     * @throws \DomainException
     */
    public function start(string $service, bool $root = false): bool
    {
        $serviceData = $this->getServiceData($service);

        if ($serviceData[self::SERVICE_STARTED] === true && $serviceData[self::SERVICE_ROOT] === $root) {
            return false;
        }

        if ($serviceData[self::SERVICE_STARTED] === true) {
            $this->stop($service);
        }

        $commandPrefix = $root ? 'sudo ' : '';
        Cli::run($commandPrefix . 'brew services start ' . $service);
        return true;
    }

    /**
     * @param string $service
     * @return void
     *
     * @throws \DomainException
     */
    public function stop(string $service): void
    {
        $serviceData = $this->getServiceData($service);

        if ($serviceData[self::SERVICE_STARTED] === true) {
            $commandPrefix = $serviceData[self::SERVICE_ROOT] ? 'sudo ' : '';
            Cli::run($commandPrefix . 'brew services stop ' . $service);
        }
    }

    /**
     * @param string $service
     * @param bool $root
     * @return void
     *
     * @throws \DomainException
     */
    public function restart(string $service, bool $root = false): void
    {
        $this->stop($service);
        $this->start($service, $root);
    }
}
