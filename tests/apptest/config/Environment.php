<?php
return [
    'Url' => [
        'Base' => 'http://localhost/',
        'Css' => 'http://localhost/',
        'Javascript' => 'http://localhost/',
        'Images' => 'http://localhost/'
    ],
    'Assets' => [
        'Html' => [
            'Compress' => true,
            'Minify' => true
        ],
        'Css' => [
            'Compress' => true,
            'Minify' => true,
            'Unify' => true
        ],
        'Javascript' => [
            'Compress' => true,
            'Minify' => true,
            'Unify' => true
        ]
    ],
    'Datasources' => [
        'Default' => [
            'Driver' => 'mysql',
            'Persistent' => false,
            'Host' => 'localhost',
            'Port' => '3306',
            'Database' => 'databasename',
            'Username' => 'root',
            'Password' => '',
            'Prefix' => '',
            'Encoding' => 'utf8',
            'Timezone' => 'UTC',
            'Flags' => []
        ]
    ],
    'Mail' => [
        'Profile' => [
            'Default' => [
                'Transport' => 'default',
                'From' => 'you@localhost',
                'Name' => 'Localhost',
                'Charset' => 'utf-8'
            ]
        ],
        'Transport' => [
            'Default' => [
                'Smtp' => false,
                'Host' => 'localhost',
                'Port' => 25,
                'Timeout' => 30,
                'Username' => null,
                'Password' => null,
                'Client' => null,
                'Secure' => null
            ]
        ]
    ],
    'Debug' => [
        'ShowDebugBar' => true,
        'Log' => [
            'Server' => [
                'Save' => true,
                'Path' => APP_LOGS,
                'Level' => ['warning', 'error', 'critical', 'alert', 'emergency']
            ],
            'Mail' => [
                'Active' => true,
                'MailSubject' => 'Error report',
                'Level' => ['error', 'critical', 'emergency'],
                'Addresses' => [
                    'suport@domain.com'
                ]
            ]
        ]
    ],
    'Session' => [
        'Type' => 'php',
        'Name' => null,
        'Timeout' => 0,
        'Path' => null
    ]
];