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
