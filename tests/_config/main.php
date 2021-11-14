<?php

use rhertogh\Yii2Oauth2Server\Oauth2Module;
use Yii2Oauth2ServerTests\_helpers\TestUserModel;

// phpcs:disable Generic.Files.LineLength.TooLong -- Sample documentation
return [

    'id' => 'testapp',
    'name' => 'Yii2 Oauth2 Server Test',
    'basePath' => dirname(__DIR__),
    'runtimePath' => dirname(__DIR__) . '/_runtime',

    'timeZone' => 'UTC',

    'vendorPath' => dirname(__DIR__, 2) . '/vendor',

    'bootstrap' => [
        'oauth2',
        'log',
    ],

    'modules' => [
        'oauth2' => [
            'class' => Oauth2Module::class,
            'identityClass' => TestUserModel::class,
            'privateKey' => '@Yii2Oauth2ServerTests/_config/keys/private.key',
            'publicKey' => '@Yii2Oauth2ServerTests/_config/keys/public.key', // Path to the public key.
            'privateKeyPassphrase' => getenv('YII2_OAUTH2_SERVER_PRIVATE_KEY_PASSPHRASE'), // The private key passphrase (if used).
            'codesEncryptionKey' => getenv('YII2_OAUTH2_SERVER_CODES_ENCRYPTION_KEY'), // The encryption key for authorization and refresh codes.
            'storageEncryptionKeys' => [
                '2021-01-01' => getenv('YII2_OAUTH2_SERVER_STORAGE_ENCRYPTION_KEY'), // The encryption key for storage like client secrets.
            ],
            'defaultStorageEncryptionKey' => '2021-01-01',
            'grantTypes' => [
                Oauth2Module::GRANT_TYPE_AUTH_CODE,
                Oauth2Module::GRANT_TYPE_CLIENT_CREDENTIALS,
                Oauth2Module::GRANT_TYPE_IMPLICIT,
                Oauth2Module::GRANT_TYPE_PASSWORD,
                Oauth2Module::GRANT_TYPE_REFRESH_TOKEN,
            ],
            'enableOpenIdConnect' => true,
            'defaultUserAccountSelection' => Oauth2Module::USER_ACCOUNT_SELECTION_UPON_CLIENT_REQUEST
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
