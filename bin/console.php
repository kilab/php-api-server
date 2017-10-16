<?php

use Kilab\Api\Console;

const BASE_DIR = __DIR__ . '/../';

$loader = require BASE_DIR . 'vendor/autoload.php';
$params = Console::parseArguments($argv);

if (!isset($params['version'])) {
    Console::fatal('Missing --version parameter. You have to specify API version.');
}

if (!isset($params['command'])) {
    Console::fatal('Missing command parameter. You have to specify command to execute as second parameter.');
}

$className = 'Kilab\Api\Command\\' . ucfirst($params['command']);

if (!class_exists($className)) {
    Console::fatal('Unknown command "' . $params['command'] . '". Please correct command name and try again.');
}

define('API_VERSION', 'V' . $params['version']);

$dbConnection = Kilab\Api\Db::instance();
$commandClass = new $className;

if (isset($params['operation'])) {
    $operation = $params['operation'];
    $commandClass->{$operation}($params);
} else {
    $commandClass->execute($params);
}
