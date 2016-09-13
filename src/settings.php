<?php
return [
    'settings' => [
        'displayErrorDetails' => true, // set to false in production
        'addContentLengthHeader' => false, // Allow the web server to send the content-length header

        // Renderer settings
        'renderer' => [
            'template_path' => __DIR__ . '/../templates/',
        ],

        // Monolog settings
        'logger' => [
            'name' => 'slim-app',
            'path' => __DIR__ . '/../logs/app.log',
            'level' => \Monolog\Logger::DEBUG,
        ],
        'db' => [
            'database_type' => (string)getenv('DB_TYPE') ?: 'mysql',
            'database_name' => (string)getenv('DB_NAME') ?: 'cald',
            'server' => 'localhost',
            'username' => (string)getenv('DB_USER') ?: 'cald',
            'password' => (string)getenv('DB_PASS') ?: 'cald',
            'charset' => 'utf8'
        ]
    ],
];
