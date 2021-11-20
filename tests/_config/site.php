<?php

use Codeception\Stub;
use yii\helpers\ArrayHelper;
use yii\web\AssetManager;
use yii\web\Request;
use Yii2Oauth2ServerTests\_helpers\NoHeadersResponse;
use Yii2Oauth2ServerTests\_helpers\TestUserComponent;
use Yii2Oauth2ServerTests\_helpers\TestUserModel;

return ArrayHelper::merge(require('main.php'), [

    'layout' => false,

    'controllerNamespace' => 'Yii2Oauth2ServerTests\\_helpers\\controllers\\web',

    'components' => [
        'user' => [
            'class' => TestUserComponent::class,
            'identityClass' => TestUserModel::class,
        ],
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'enableStrictParsing' => true,
            'rules' => [
                'test/api/<action>' => 'test-api/<action>'
            ],
        ],
        'request' => [
            'class' => Request::class,
            'cookieValidationKey' => 'secret',
        ],
        'response' => [
            'class' => NoHeadersResponse::class,
        ],
        'db' => [
            'class' => yii\db\Connection::class,
            'dsn' => getenv('MYSQL_HOST') && getenv('MYSQL_DB_NAME')
                ? 'mysql:host=' . getenv('MYSQL_HOST') . (getenv('MYSQL_PORT') ? ':' . getenv('MYSQL_PORT') : '')
                  . ';dbname=' . getenv('MYSQL_DB_NAME')
                : null,
            'username' => getenv('MYSQL_USER_NAME'),
            'password' => getenv('MYSQL_USER_PASSWORD'),
            'charset' => 'utf8mb4',
            'enableSchemaCache' => false,
            'enableLogging' => YII_DEBUG,
            'enableProfiling' => YII_DEBUG,
        ],
        'errorHandler' => [
            'silentExitOnException' => true,
        ],
        'assetManager' => Stub::construct(AssetManager::class, [], [
            'init' => function () {
                parent::init();
            },
            'publish' => function ($path, $options = []) {
                return ['', ''];
            },
        ]),
    ],
]);
