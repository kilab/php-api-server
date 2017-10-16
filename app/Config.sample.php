<?php

return [
    'Database' => [
        'Driver' => 'pdo_mysql',
        'Host' => '',
        'Name' => '',
        'User' => '',
        'Password' => '',
    ],
    'Debug'    => true,
    'Default'  => [
        'Entity' => 'index',
    ],
    'Entity'   => [
        'CamelCaseFieldNames' => true,
    ],
    'Logger'   => [
        'Enabled' => true,
        'Mail' => [
            'Enabled' => false,
            'Protocol' => 'SMTP',
            'Host' => '',
            'User' => '',
            'Password' => '',
            'Port' => 587,
            'RecipientAddress' => '',
        ]
    ],
    'Response' => [
        'Headers' => [
            'Access-Control-Allow-Credentials' => 'false',
            'Access-Control-Allow-Headers' => 'x-http-method-override, x-callback',
            'Access-Control-Allow-Methods' => 'OPTIONS, GET, POST, PUT, DELETE',
            'Access-Control-Allow-Origin' => '*',
            'Vary' => '',
        ]
    ],
];
