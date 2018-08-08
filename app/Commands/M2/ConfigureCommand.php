<?php

namespace App\Commands\M2;

use App\Command;
use App\Facades\File;
use App\Facades\Cli;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Collection;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Process\Exception\ProcessFailedException;

class ConfigureCommand extends Command
{
    const COMMAND = 'm2:configure';

    /**
     * @var string
     */
    protected $signature = self::COMMAND
        . ' {config? : Config file name}'
        . ' {--m|magento-path= : Magento root path}'
        . ' {--s|scope= : Overwrite configuration scope (default, website, or store)}'
        . ' {--c|scope-code= : Overwrite scope code (required only if scope is not \'default\')}';

    /**
     * @var string
     */
    protected $description = 'Configure Magento 2 from config fixture';

    /**
     * @return void
     */
    public function handle(): void
    {
        $magentoPath = $this->option('magento-path');

        $magentoPath = $this->getCurrentPath($magentoPath);
        if (!$this->verifyPath($magentoPath, false)) {
            $this->error('Passed path does not exist or not a folder: ' . $magentoPath);
            return;
        }

        $config = $this->argument('config') ?: $this->getConfig();
        if ($config === null) {
            return;
        }

        $configFilePath = $this->getFilePath($config, config('env.m2.configs_path'));
        if (!$this->verifyPath($configFilePath)) {
            $this->error(sprintf('Passed path does not exist or not a file: <comment>%s</comment>', $configFilePath));
            return;
        }

        $this->info(sprintf('Importing config from <comment>%s</comment>', $configFilePath));

        try {
            $configData = $this->readFile($configFilePath);
        } catch (\RuntimeException $e) {
            $this->error($e->getMessage());
            return;
        }

        $configurations = $this->parseConfig($configData);
        $this->importConfig($configurations);

        $this->info('Import finished');

        $this->task('Flush Magento cache', function () use ($magentoPath) {
            $this->runMagentoCommand('cache:flush');
        });
    }

    /**
     * @param string $command
     * @return string
     */
    private function runMagentoCommand(string $command): string
    {
        $magentoPath = $this->getCurrentPath($this->option('magento-path'));
        return Cli::run(sprintf('php %s/bin/magento %s', $magentoPath, $command));
    }

    /**
     * @param array $configurations
     * @return void
     */
    private function importConfig(array $configurations): void
    {
        $minPathLength = $this->getMaxLength($configurations, 'path');
        $minScopeLength = $this->getMaxLength($configurations, 'scope', 7);
        $minScopeCodeLength = $this->getMaxLength($configurations, 'scope-code');

        foreach ($configurations as $configData) {
            $path = $configData['path'];
            $value = $configData['value'];
            $scope = $this->option('scope') ?: $configData['scope'];
            $scopeCode = $this->option('scope-code') ?: $configData['scope-code'];
            $scopeCode = $scope === 'default' && empty($scopeCode) ? '0' : $scopeCode;

            $scopeComment = sprintf("[%s: %s]", $scope, $scopeCode);
            $scopeLength = $minScopeLength + $minScopeCodeLength + 4;
            $this->task(
                sprintf("%-{$scopeLength}s %-{$minPathLength}s %s", $scopeComment, $path, $value),
                function () use ($path, $value, $scope, $scopeCode) {
                    $scopeOptions = $scope !== 'default' ? sprintf('--scope %s --scope-code %s', $scope, $scopeCode) : '';
                    try {
                        $output = $this->runMagentoCommand(sprintf('config:set %s "%s" "%s"', $scopeOptions, $path, $value));
                        $result = $output === 'Value was saved.' . PHP_EOL ? true : $output;
                    } catch (ProcessFailedException $e) {
                        $result = $this->errorText(trim($e->getProcess()->getOutput()));
                    }
                    return $result;
                }
            );
        }
    }

    /**
     * @param string $configFilePath
     * @return array
     */
    private function readFile(string $configFilePath): array
    {
        $fileExtension = File::extension($configFilePath);
        switch ($fileExtension) {
            case 'yaml':
            case 'yml':
                $configData = Yaml::parse(File::get($configFilePath));
                break;
            case 'php':
                $configData = include_once $configFilePath;
                break;
            default:
                throw new \RuntimeException(sprintf('[%s] extension is not supported, so the file cannot be imported: %s', $fileExtension, $configFilePath));
        }

        if (empty($configData) || !is_array($configData)) {
            throw new \RuntimeException(sprintf('Invalid php config: <comment>%s</comment>', $configFilePath));
        }

        return $configData;
    }

    /**
     * @param array $configData
     * @return array
     */
    private function parseConfig(array $configData): array
    {
        $configurations = count(explode('/', key($configData))) === 3
            ? $this->parseFlatConfig($configData)
            : $this->parseNestedConfig($configData);

        return $configurations;
    }

    /**
     * @param array $configData
     * @return array|null
     */
    private function parseNestedConfig(array $configData): array
    {
        $configurations = [];
        foreach ($configData as $scope => $scopeData) {
            if ($scope === 'default' && key($scopeData) != '0') {
                $scopeCode = '0';
                $configurations = array_merge($configurations, $this->parseNestedConfigValues($scope, $scopeCode, $scopeData));
                continue;
            }
            $scope = $scope === 'stores' ? 'store' : $scope;
            $scope = $scope === 'websites' ? 'website' : $scope;
            foreach ($scopeData as $scopeCode => $configData) {
                $configurations = array_merge($configurations, $this->parseNestedConfigValues($scope, $scopeCode, $configData));
            }
        }

        return $configurations;
    }

    /**
     * @param string $scope
     * @param string $scopeCode
     * @param array $configData
     * @return array|null
     */
    private function parseNestedConfigValues(string $scope, string $scopeCode, array $configData): array
    {
        $configurations = [];
        foreach ($configData as $moduleSpace => $moduleConfig) {
            foreach ($moduleConfig as $section => $sectionData) {
                foreach ($sectionData as $configCode => $value) {
                    $configurations[] = [
                        'path' => implode('/', [$moduleSpace, $section, $configCode]),
                        'value' => $value,
                        'scope' => $scope,
                        'scope-code' => $scopeCode,
                    ];
                }
            }
        }
        return $configurations;
    }

    /**
     * @param array $configData
     * @return array|null
     */
    private function parseFlatConfig(array $configData): array
    {
        $configurations = [];
        foreach ($configData as $configPath => $valuesPerScope) {
            if (empty($valuesPerScope) || !is_array($valuesPerScope)) {
                $this->error(sprintf('No config values for <comment>%s</comment>', $configPath));
                continue;
            }

            $configurations = array_merge($configurations, $this->parseFlatConfigValues($configPath, $valuesPerScope));
        }

        return $configurations;
    }

    /**
     * @param string $configPath
     * @param mixed[] $valuesPerScope
     * @return string[][]
     */
    private function parseFlatConfigValues($configPath, array $valuesPerScope): array
    {
        $result = [];
        foreach ($valuesPerScope as $scope => $scopeValue) {
            if (empty($scopeValue) || !is_array($scopeValue)) {
                $this->error(sprintf('No config values for <comment>%s</comment>: <comment>%s</comment>', $configPath));
                continue;
            }

            foreach ($scopeValue as $scopeCode => $value) {
                $result[] = [
                    'path' => $configPath,
                    'value' => $value,
                    'scope' => $scope,
                    'scope-code' => $scopeCode,
                ];
            }
        }

        return $result;
    }

    /**
     * @param array $array
     * @param string $field
     * @param int $min
     * @return int
     */
    private function getMaxLength(array $array, string $field, $min = 0): int
    {
        $lengths = array_map(function ($element) use ($field) {
            return mb_strlen($element[$field]);
        }, $array);
        $length = max($lengths);
        return $length > $min ? $length : $min;
    }

    /**
     * @return string|null
     */
    private function getConfig(): ?string
    {
        $configs = $this->getConfigsList();

        if (empty($configs)) {
            $this->warn('Nothing found.');
            return null;
        }

        return $this->menu('Setup config from:', $configs);
    }

    /**
     * @return array
     */
    private function getConfigsList(): array
    {
        /** @var \League\Flysystem\Filesystem $configDisk */
        $configDisk = Storage::disk('m2_configs')->getDriver();

        $files = $configDisk->listContents();

        $collection = Collection::make($files);
        $collection = $collection->where('type', 'file');

        $collection = $collection->mapWithKeys(function ($file) {
            return [
                $file['basename'] => $file['basename']
            ];
        });
        return $collection->all();
    }
}
