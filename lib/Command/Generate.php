<?php

namespace Kilab\Api\Command;

use Kilab\Api\Config;
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
        Console::write('php bin/console.php generate all --name=Order', 'yellow');
    }

    /**
     * Wrapper to call all operation in one command.
     *
     * @param array $params
     *
     * @throws \LogicException
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
     *
     * @throws \LogicException
     */
    public function entity(array $params): void
    {
        if (!isset($params['name'])) {
            Console::fatal('Missing name parameter.');
        }

        $entityName = studly_case($params['name']);
        $options = [
            'entityName' => $entityName,
            'className'  => 'App\\Entity\\' . $entityName,
            'fileKind'   => 'entity',
            'replace'    => [
                'tags'   => ['{entityName}'],
                'values' => [$entityName],
            ],
            'filePath'   => 'Entity/' . $entityName . '.php',
        ];

        $this->createFile($options);
    }

    /**
     * Create entity schema file.
     *
     * @param array $params
     *
     * @throws \LogicException
     */
    public function schema(array $params): void
    {
        if (!isset($params['name'])) {
            Console::fatal('Missing name parameter.');
        }

        $entityName = studly_case($params['name']);
        $options = [
            'entityName' => $entityName,
            'className'  => 'App\\Entity\Schema\\' . $entityName,
            'fileKind'   => 'schema',
            'replace'    => [
                'tags'   => ['{entityName}', '{entityTable}'],
                'values' => [$entityName, str_plural(snake_case($entityName))],
            ],
            'filePath'   => 'Entity/Schema/' . $entityName . '.php',
        ];

        $this->createFile($options);
    }

    /**
     * Create entity controller file.
     *
     * @param array $params
     *
     * @throws \LogicException
     */
    public function controller(array $params): void
    {
        if (!isset($params['name'])) {
            Console::fatal('Missing name parameter.');
        }

        $entityName = studly_case($params['name']);
        $options = [
            'entityName' => $entityName,
            'className'  => 'App\\Controller\\' . str_plural($entityName) . 'Controller',
            'fileKind'   => 'controller',
            'replace'    => [
                'tags'   => ['{entityName}', '{controllerName}'],
                'values' => [$entityName, str_plural($entityName)],
            ],
            'filePath'   => 'Controller/' . str_plural($entityName) . 'Controller.php',
        ];

        $this->createFile($options);
    }

    /**
     * Create and save generated file.
     *
     * @param array $options
     *
     * @throws \LogicException
     */
    private function createFile(array $options): void
    {
        if (class_exists($options['className'])) {
            Console::fatal($options['entityName'] . ' ' . $options['fileKind'] . ' file already exists.');
        }

        Console::write('Generating ' . $options['entityName'] . ' ' . $options['fileKind'] . ' file..');

        $templateContent = $this->getFileTemplate($options['fileKind']);
        $fileContent = str_replace($options['replace']['tags'], $options['replace']['values'], $templateContent);

        if (file_put_contents(Config::get('BaseDir') . 'app/' . $options['filePath'], $fileContent)) {
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
     * @throws \LogicException
     */
    private function getFileTemplate(string $file): string
    {
        $filePath = Config::get('BaseDir') . 'lib/Command/stubs/' . $file . '.stub';

        if (!file_exists($filePath)) {
            Console::fatal('Missing stub file for: ' . $file . '.');
        }

        return file_get_contents($filePath);
    }
}
