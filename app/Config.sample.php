<?php

return [
    'Database' => [
        'Host' => '',
        'Name' => '',
        'User' => '',
        'Password' => '',
    ],
    'Default' => [
        'Resource' => 'index',
    ],
    'Logger' => [
        'Enabled' => true,
        'Mail' => [
            'Protocol' => 'SMTP',
            'Host' => '',
            'User' => '',
            'Password' => '',
            'Port' => 587,
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
    ]
];
