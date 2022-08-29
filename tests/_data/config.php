<?php

$config = [
    'databases' => [
        'cubrid' => [
            'dsn' => 'cubrid:dbname=demodb;host=cubrid;port=33000',
            'username' => 'dba',
            'password' => '',
            'fixture' => __DIR__ . '/cubrid.sql',
        ],
        'mysql' => [
            'connection' => [
                'dsn' =>
                    'mysql:host=' . getenv('MYSQL_HOST')
                    . (getenv('MYSQL_PORT') ? ':' . getenv('MYSQL_PORT') : '')
                    . ';dbname=' . getenv('MYSQL_DB_NAME'),
                'username' => getenv('MYSQL_USER_NAME'),
                'password' => getenv('MYSQL_USER_PASSWORD'),
                'charset' => 'utf8mb4',
            ],
            'preMigrationsFixtures' => [
                __DIR__ . '/mysql_pre.sql',
                ...(is_file(__DIR__ . '/mysql_pre.local.sql') ? [__DIR__ . '/mysql_pre.local.sql'] : []),
            ],
            'migrations' => [
                'migrationNamespaces' => [
                    'rhertogh\\Yii2Oauth2Server\\migrations',
                ],
            ],
            'postMigrationsFixtures' => [
                __DIR__ . '/mysql_post.sql',
                ...(is_file(__DIR__ . '/mysql_post.local.sql') ? [__DIR__ . '/mysql_post.local.sql'] : []),
            ],
        ],
        'sqlite' => [
            'connection' => [
                'dsn' => 'sqlite::memory:',
            ],
            'fixture' => __DIR__ . '/sqlite.sql',
        ],
        'sqlsrv' => [
            'connection' => [
                'dsn' => 'sqlsrv:Server=mssql;Database=yii2test',
                'username' => 'sa',
                'password' => 'Microsoft-12345',
            ],
            'fixture' => __DIR__ . '/mssql.sql',
        ],
        'pgsql' => [
            'connection' => [
                'dsn' => 'pgsql:host=postgres;dbname=yiitest;port=5432;',
                'username' => 'postgres',
                'password' => 'postgres',
            ],
            'fixture' => __DIR__ . '/postgres.sql',
        ],
        'oci' => [
            'connection' => [
                'dsn' => 'oci:dbname=LOCAL_XE;charset=AL32UTF8;',
                'username' => '',
                'password' => '',
            ],
            'fixture' => __DIR__ . '/oci.sql',
        ],
    ],
];

if (is_file(__DIR__ . '/config.local.php')) {
    include __DIR__ . '/config.local.php';
}

return $config;
