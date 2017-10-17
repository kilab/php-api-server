<?php

namespace Kilab\Api\Command;

use Kilab\Api\Console;
use ReflectionMethod;

class Generate
{
    public function execute(): void
    {
        $availableOperations = [];

        foreach (get_class_methods($this) as $method) {
            $reflection = new ReflectionMethod($this, $method);

            if ($reflection->getName() !== 'execute' && $reflection->isPublic()) {
                $availableOperations[] = $reflection->getName();
            }
        }

        if (count($availableOperations) === 0) {
            Console::fatal('No any public methods in Command/Db/Schema class.');
        }

        Console::write('Please specify one of available operation: ');

        $separator = "\033[" . Console::colorCode('default') . 'm, ' . "\033[" . Console::colorCode('yellow') . 'm';

        Console::write(implode($separator, $availableOperations), 'yellow');
        Console::write('.' . PHP_EOL . PHP_EOL);

        Console::write('Example command call: ');
        Console::write('php bin/console.php generate all --name=Order --version=1', 'yellow');
    }

    /**
     * Wrapper to call all operation in one command.
     *
     * @param array $params
     */
    public function all(array $params): void
    {
        $startTime = microtime(true);

        $this->entity($params);
        $this->schema($params);
        $this->controller($params);

        Console::success(PHP_EOL . sprintf('Operation finished in %fs.', microtime(true) - $startTime));
    }

    /**
     * Create entity file.
     *
     * @param array $params
     */
    public function entity(array $params): void
    {
        if (!isset($params['name'])) {
            Console::fatal('Missing name parameter.');
        }

        $entityName = studly_case($params['name']);
        $options = [
            'entityName' => $entityName,
            'className'  => 'App\\' . API_VERSION . '\Entity\\' . $entityName,
            'fileKind'   => 'entity',
            'replace'    => [
                'tags'   => ['{apiVersion}', '{entityName}'],
                'values' => [API_VERSION, $entityName],
            ],
            'filePath'   => API_VERSION . '/Entity/' . $entityName . '.php',
        ];

        $this->createFile($options);
    }

    /**
     * Create entity schema file.
     *
     * @param array $params
     */
    public function schema(array $params): void
    {
        if (!isset($params['name'])) {
            Console::fatal('Missing name parameter.');
        }

        $entityName = studly_case($params['name']);
        $options = [
            'entityName' => $entityName,
            'className'  => 'App\\' . API_VERSION . '\Entity\Schema\\' . $entityName,
            'fileKind'   => 'schema',
            'replace'    => [
                'tags'   => ['{apiVersion}', '{entityName}', '{entityTable}'],
                'values' => [API_VERSION, $entityName, str_plural(snake_case($entityName))],
            ],
            'filePath'   => API_VERSION . '/Entity/Schema/' . $entityName . '.php',
        ];

        $this->createFile($options);
    }

    /**
     * Create entity controller file.
     *
     * @param array $params
     */
    public function controller(array $params): void
    {
        if (!isset($params['name'])) {
            Console::fatal('Missing name parameter.');
        }

        $entityName = studly_case($params['name']);
        $options = [
            'entityName' => $entityName,
            'className'  => 'App\\' . API_VERSION . '\Controller\\' . str_plural($entityName) . 'Controller',
            'fileKind'   => 'controller',
            'replace'    => [
                'tags'   => ['{apiVersion}', '{entityName}', '{controllerName}'],
                'values' => [API_VERSION, $entityName, str_plural($entityName)],
            ],
            'filePath'   => API_VERSION . '/Controller/' . str_plural($entityName) . 'Controller.php',
        ];

        $this->createFile($options);
    }

    /**
     * Create and save generated file.
     *
     * @param array $options
     */
    private function createFile(array $options): void
    {
        if (class_exists($options['className'])) {
            Console::fatal($options['entityName'] . ' ' . $options['fileKind'] . ' file already exists.');
        }

        Console::write('Generating ' . $options['entityName'] . ' ' . $options['fileKind'] . ' file..');

        $templateContent = $this->getFileTemplate($options['fileKind']);
        $fileContent = str_replace($options['replace']['tags'], $options['replace']['values'], $templateContent);

        if (file_put_contents(BASE_DIR . 'app/' . $options['filePath'], $fileContent)) {
            Console::success('[OK]' . PHP_EOL);
        } else {
            Console::error('[FAIL]' . PHP_EOL);
        }
    }

    /**
     * Get file template content for given file kind.
     *
     * @param string $file
     *
     * @return string
     */
    private function getFileTemplate(string $file): string
    {
        $filePath = BASE_DIR . 'lib/Command/stubs/' . $file . '.stub';

        if (!file_exists($filePath)) {
            Console::fatal('Missing stub file for: ' . $file . '.');
        }

        return file_get_contents($filePath);
    }
}