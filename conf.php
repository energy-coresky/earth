<?php

$plans = [
    'view' => ['path' => __DIR__ . '/w3'],
    'glob' => ['path' => __DIR__ . '/w3'],
    'app' => [
        'type' => 'dev',
        'require' => 'SQLite3 Parsedown',
    ],
];

SKY::$databases += [
    '_e' => ['driver' => 'sqlite3', 'dsn' => __DIR__ . '/earth.base'],
];
