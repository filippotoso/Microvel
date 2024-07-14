<?php

return [

    'paths' => [
        'resources' => realpath(__DIR__ . '/../resources'),
        'storage' => realpath(__DIR__ . '/../storage'),
    ],

    'app' => [
        'timezone' => 'Europe/Rome',
    ],

    'database' => [
        'driver' => 'mysql',
        'host' => '127.0.0.1',
        'port' => '3306',
        'database' => 'database',
        'username' => 'root',
        'password' => '',
        'unix_socket' => '',
        'charset' => 'utf8mb4',
        'collation' => 'utf8mb4_unicode_ci',
        'prefix' => '',
        'strict' => true,
        'engine' => null,
    ],

    'filesystems' => [
        'default' => 'local',

        'disks' => [
            'local' => [
                'driver' => 'local',
                'root' => __DIR__ . '/../storage',
            ],
        ]
    ]
];
