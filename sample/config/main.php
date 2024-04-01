<?php

/// WARNING! This configuration is optimized for local development and should NOT be used in any other environment
/// (for both security and performance)!

use rhertogh\Yii2Oauth2Server\interfaces\components\openidconnect\scope\Oauth2OidcScopeCollectionInterface;
use rhertogh\Yii2Oauth2Server\Oauth2Module;
use sample\components\AppBootstrap;
use yii\base\InvalidConfigException;
use yii\log\Logger;

$dbDriver = strtolower(getenv('YII_DB_DRIVER'));

if ($dbDriver === 'mysql') {
    $db = [
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
    ];
} elseif ($dbDriver === 'postgresql') {
    $db = [
        'class' => yii\db\Connection::class,
        'dsn' =>
            'pgsql:host=' . getenv('POSTGRES_HOST')
            . (getenv('POSTGRES_PORT') ? ';port=' . getenv('POSTGRES_PORT') : '')
            . ';dbname=' . getenv('POSTGRES_DB'),
        'username' => getenv('POSTGRES_USER'),
        'password' => getenv('POSTGRES_PASSWORD'),
        'charset' => 'utf8',
        'enableSchemaCache' => true,
        'schemaCacheDuration' => 0, // never expire.
        'enableLogging' => YII_DEBUG,
        'enableProfiling' => YII_DEBUG,
    ];
} elseif ($dbDriver === 'sqlite') {
    $db = [
        'class' => yii\db\Connection::class,
        'dsn' => 'sqlite:' . getenv('SQLITE_DB_FILE'),
        'charset' => 'utf8',
        'enableSchemaCache' => true,
        'schemaCacheDuration' => 0, // never expire.
        'enableLogging' => YII_DEBUG,
        'enableProfiling' => YII_DEBUG,
    ];
} else {
    throw new InvalidConfigException("Unknown database driver '$dbDriver'.");
}

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
            'nonTlsAllowedRanges' => YII_DEBUG ? ['localhost', 'private'] : 'localhost', // By default, Clients are only allowed to connect using TLS, ranges specified here are exempted form this restriction.
            'grantTypes' => [ // For more information which grant types to use, please see https://oauth2.thephpleague.com/authorization-server/which-grant/.
                Oauth2Module::GRANT_TYPE_AUTH_CODE,
                Oauth2Module::GRANT_TYPE_CLIENT_CREDENTIALS,
                Oauth2Module::GRANT_TYPE_IMPLICIT,
                Oauth2Module::GRANT_TYPE_PASSWORD,
                Oauth2Module::GRANT_TYPE_REFRESH_TOKEN,
                Oauth2Module::GRANT_TYPE_PERSONAL_ACCESS_TOKEN,
            ],
            'migrationsNamespace' => 'sample\\migrations\\oauth2',  // The namespace with which migrations will be created (and by which they will be located).
            'defaultUserAccountSelection' => Oauth2Module::USER_ACCOUNT_SELECTION_UPON_CLIENT_REQUEST, // Allow clients to request user account selection (OpenID Connect).
            'defaultAccessTokenTTL' => 'PT2H', // Set the default Access Token TTL if the grant type doesn't specify its own TTL (e.g. the Personal Access Token grant has its own TTL of 1 year).
            'migrationsFileOwnership' => '1000:1000', // The file ownership for generated migrations.
            'migrationsFileMode' => 0660, // The file access mode for generated migrations.
            'clientRedirectUrisEnvVarConfig' => [ // Enable environment variable substitution in oauth2 clients `redirect_uris`.
                'allowList' => ['*'], // ⚠️ WARNING: Setting `allowList` to `['*']` allows all environment variables to be used, this is only used as an example and should be replaced by an actual list of allowed environment variables.
            ],
            'exceptionOnInvalidScope' => YII_DEBUG, // Throw an exception when a Client requests an unknown or unauthorized scope (would be silently ignored otherwise).
            // OpenID Connect specific settings (Only required if OpenID Connect support is required).
            'enableOpenIdConnect' => true, // Enable OpenID Connect support.
            'openIdConnectScopes' => [ // Optional, list of enabled OpenID Connect Scopes.
                ...Oauth2OidcScopeCollectionInterface::OPENID_CONNECT_DEFAULT_SCOPES, // Include the default OpenID Connect scopes.
                'my_custom_oidc_scope' => [ // Add a custom scope.
                    'my_custom_oidc_claim' => 'customOpenIdConnectClaimProperty', // Add a custom claim.
                ],
            ],
            'openIdConnectRpInitiatedLogoutEndpoint' => true, // Optional, enable the OpenID Connect end session endpoint for Single Sign Out.
            'httpClientErrorsLogLevel' => Logger::LEVEL_ERROR, // Optional, defaults to "LEVEL_ERROR" when YII_DEBUG is `true` or "LEVEL_INFO" when YII_DEBUG `false`. Set to `0` to completely disable logging for HTTP client errors.
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
        'db' => $db,
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
