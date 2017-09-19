<?php

$loader = require __DIR__ . '/../vendor/autoload.php';
$request = new Kilab\Api\Request();


$apiServer = new Kilab\Api\Server($request);
$apiServer->run();
