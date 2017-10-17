<?php

const BASE_DIR = __DIR__ . '/../';

$requestServerInfo = $_SERVER;

// determine API version to load
preg_match('/v\d/i', $requestServerInfo['REQUEST_URI'], $matches);

if (!empty($matches)) {
    define('API_VERSION', strtolower($matches[0]));
} else {
    header('Location: /v1');
}

$loader = require BASE_DIR . 'vendor/autoload.php';

$errorHandler = new Kilab\Api\ErrorHandler($requestServerInfo);
$request = new Kilab\Api\Request($requestServerInfo);
$database = Kilab\Api\Db::instance();

// run server
$apiServer = new Kilab\Api\Server($request);
$apiServer->run();
