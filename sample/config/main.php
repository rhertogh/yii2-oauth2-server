<?php

/// WARNING! This configuration is optimized for local development and should NOT be used in any other environment
/// (for both security and performance)!

use rhertogh\Yii2Oauth2Server\Oauth2Module;
use sample\components\AppBootstrap;

// phpcs:disable Generic.Files.LineLength.TooLong -- Sample documentation
return [

    'id' => 'Yii2Oauth2Server',
    'name' => 'Yii2 Oauth2 Server',
    'basePath' => dirname(__DIR__),

    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm'   => '@vendor/npm-asset',
    ],

    'timeZone' => 'UTC',

    'vendorPath' => dirname(dirname(__DIR__)) . '/vendor',

    'bootstrap' => [
        AppBootstrap::class,
        'oauth2',
        'log',
    ],

    'modules' => [
        'oauth2' => [
            'class' => rhertogh\Yii2Oauth2Server\Oauth2Module::class,
            'identityClass' => sample\models\User::class, // The Identity Class of your application (most likely the same as the 'identityClass' of your application's User Component).
            'privateKey' => '@app/config/keys/private.key', // Path to the private key.
            'publicKey' => '@app/config/keys/public.key', // Path to the public key.
            'privateKeyPassphrase' => getenv('YII2_OAUTH2_SERVER_PRIVATE_KEY_PASSPHRASE'), // The private key passphrase (if used).
            'codesEncryptionKey' => getenv('YII2_OAUTH2_SERVER_CODES_ENCRYPTION_KEY'), // The encryption key for authorization and refresh codes.
            'storageEncryptionKeys' => getenv('YII2_OAUTH2_SERVER_STORAGE_ENCRYPTION_KEYS'), // The encryption key for storage like client secrets.
            'defaultStorageEncryptionKey' => '2022-01-01', // The index of the default key in storageEncryptionKeys.
            'grantTypes' => [ // For more information which grant types to use, please see https://oauth2.thephpleague.com/authorization-server/which-grant/.
                Oauth2Module::GRANT_TYPE_AUTH_CODE,
                Oauth2Module::GRANT_TYPE_CLIENT_CREDENTIALS,
                Oauth2Module::GRANT_TYPE_IMPLICIT,
                Oauth2Module::GRANT_TYPE_PASSWORD,
                Oauth2Module::GRANT_TYPE_REFRESH_TOKEN,
                Oauth2Module::GRANT_TYPE_PERSONAL_ACCESS_TOKEN,
            ],
            'migrationsNamespace' => 'sample\\migrations\\oauth2',  // The namespace with which migrations will be created (and by which they will be located).
            'enableOpenIdConnect' => true, // Only required if OpenID Connect support is required.
            'defaultUserAccountSelection' => Oauth2Module::USER_ACCOUNT_SELECTION_UPON_CLIENT_REQUEST, // Allow clients to request user account selection (OpenID Connect).
            'defaultAccessTokenTTL' => 'PT2H', // Set the default Access Token TTL if the grant type doesn't specify its own TTL (e.g. the Personal Access Token grant has its own TTL of 1 year).
            'migrationsFileOwnership' => '1000:1000', // The file ownership for generated migrations.
            'migrationsFileMode' => 0660, // The file access mode for generated migrations.
        ],
    ],

    'components' => [
        'security' => [
            'class' => \yii\base\Security::class,
        ],
        'cache' => [
            'class' => yii\caching\DummyCache::class,
            'serializer' => false,
        ],
        'db' => [
            'class' => yii\db\Connection::class,
            'dsn' => getenv('MYSQL_HOST') && getenv('MYSQL_DB_NAME')
                ? 'mysql:host=' . getenv('MYSQL_HOST')
                  . (getenv('MYSQL_PORT') ? ';port=' . getenv('MYSQL_PORT') : '')
                  . ';dbname=' . getenv('MYSQL_DB_NAME')
                : null,
            'username' => getenv('MYSQL_USER_NAME'),
            'password' => getenv('MYSQL_USER_PASSWORD'),
            'charset' => 'utf8mb4',
            'enableSchemaCache' => true,
            'schemaCacheDuration' => 0, // never expire.
            'enableLogging' => YII_DEBUG,
            'enableProfiling' => YII_DEBUG,
        ],
        'log' => [
            'traceLevel' => 10,
            'flushInterval' => 1,
            'targets' => [
                'file' => [
                    'class' => yii\log\FileTarget::class,
                    'exportInterval' => 1,
                    'levels' => ['error', 'warning', 'info', 'trace'],
                ],
            ],
        ],
    ],
];
