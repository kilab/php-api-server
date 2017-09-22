<?php

const BASE_DIR = __DIR__ . '/../';

$loader = require BASE_DIR . 'vendor/autoload.php';

// TODO: improve passing API version to config
Kilab\Api\Config::setApiVersion($_SERVER);

$errorHandler = new Kilab\Api\ErrorHandler();
$request = new Kilab\Api\Request();

$apiServer = new Kilab\Api\Server($request);
$apiServer->run();
