<?php

namespace Kilab\Api\Command\Db;

use Illuminate\Database\Capsule\Manager;
use Illuminate\Database\Schema\Blueprint;
use Kilab\Api\Config;
use Kilab\Api\Console;
use ReflectionClass;
use ReflectionMethod;

class Schema
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
        Console::write('php bin/console.php schema update', 'yellow');
    }

    /**
     * Create new database structure from migration files.
     * Warning: This operation will erase current tables if exists.
     *
     * @throws \LogicException
     * @throws \ReflectionException
     */
    public function create(): void
    {
        Console::warning('Warning! This operation will delete database content. Do you want to continue?');

        $userAnswer = strtolower(Console::input(' [y/N]: '));

        if ($userAnswer !== 'y') {
            return;
        }

        $startTime = microtime(true);

        Console::write('Dropping current database content.. ');

        if (!empty(Manager::connection()->select('SHOW TABLES'))) {
            Manager::schema()->dropAllTables();
            Console::success('[OK]' . PHP_EOL);
        } else {
            Console::write('[IGNORE]' . PHP_EOL);
        }

        Console::write('Reading database structure from files.. ');

        $schemaToBuild = $this->loadSchemaFromFiles();

        if (empty($schemaToBuild)) {
            Console::error('[FAIL]');
            Console::fatal('Not found any schema class! Please create classes and try again.');
        } else {
            Console::success('[OK]');
        }

        Console::writeLine(PHP_EOL);

        /** @var string[][] $schema */
        foreach ($schemaToBuild as $schema) {
            /** @var $structure Blueprint */
            $structure = $schema['structure'];
            $schemaConnection = Manager::schema()->getConnection();

            Console::write('Creating table ' . $structure->getTable() . '.. ');

            $structure->create();
            $structure->build($schemaConnection, $schemaConnection->getSchemaGrammar());

            if (!empty($schema['foreigns'])) {
                foreach ($schema['foreigns'] as $foreign) {
                    Manager::schema()->table($structure->getTable(), function ($table) use ($foreign) {
                        $table->foreign($foreign[0])->references($foreign[1])->on($foreign[2]);
                    });
                }
            }

            Console::success('[OK]' . PHP_EOL);
        }

        $finishMessage = sprintf('Operation finished in %fs. %d tables were created.',
            microtime(true) - $startTime,
            count($schemaToBuild)
        );

        Console::success(PHP_EOL . $finishMessage);
    }

    /**
     * Update database structure from migration files without erase current data.
     */
    public
    function update(): void
    {
        Console::info('Not implemented yet.');
    }

    /**
     * Load schema from files in /Schema directory.
     *
     * @return array
     * @throws \LogicException
     * @throws \ReflectionException
     */
    private
    function loadSchemaFromFiles(): array
    {
        $schemas = [];

        foreach (glob(Config::get('BaseDir') . 'app/Entity/Schema/*.php') as $filePath) {
            $filePath = explode('/', $filePath);
            $className = rtrim(end($filePath), '.php');
            $className = '\App\Entity\Schema\\' . $className;

            $schemaClass = new $className();
            $reflection = new ReflectionClass($className);

            $schema['structure'] = $reflection->getProperty('structure')->getValue($schemaClass);

            if ($reflection->hasProperty('foreigns')) {
                $schema['foreigns'] = $reflection->getProperty('foreigns')->getValue($schemaClass);
            } else {
                $schema['foreigns'] = [];
            }

            $schemas[] = $schema;
        }

        return $schemas;
    }
}
