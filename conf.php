<?php

return [
    'view' => ['path' => __DIR__ . '/mvc'],
    'cfg' => ['path' => __DIR__ . '/mvc'],
    'app' => [
        'type' => 'dev',
        'require' => 'SQLite3 Parsedown',
        'databases' => [
            '_e' => ['driver' => 'sqlite3', 'dsn' => __DIR__ . '/earth.base'],
        ],
    ],
];
