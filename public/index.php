<?php

const BASE_DIR = __DIR__ . '/../';

// determine API version to load
preg_match('/v\d/i', $_SERVER['REQUEST_URI'], $matches);

if (!empty($matches)) {
    define('API_VERSION', strtolower($matches[0]));
} else {
    header('Location: /v1');
}

$loader = require BASE_DIR . 'vendor/autoload.php';

$errorHandler = new Kilab\Api\ErrorHandler();
$request = new Kilab\Api\Request($_GET, $_POST, [], [], [], $_SERVER);
$database = Kilab\Api\Db::instance();

// run server
$apiServer = new Kilab\Api\Server($request);
$apiServer->run();
