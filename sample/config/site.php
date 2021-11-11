<?php
/// WARNING! This configuration is optimized for local development and should not be used in any other environment (for both security and performance) ///

use sample\components\UserComponent;
use sample\modules\api\ApiModule;
use yii\helpers\ArrayHelper;
use yii\web\Request;
use yii\web\User;
use sample\models\User as UserIdentity;

return ArrayHelper::merge(require('main.php'), [

    'bootstrap' => [
        'debug',
    ],

    'controllerNamespace' => 'sample\controllers',

    'defaultRoute' => 'default/index',

    'modules' => [
        'api' => [
            'class' => ApiModule::class, // Just a sample api
        ],
        'debug' => [
            'class' => yii\debug\Module::class,
            'allowedIPs' => ['*'],
        ],
    ],

    'components' => [
        'user' => [
            'class' => UserComponent::class,
            'identityClass' => UserIdentity::class,
            'loginUrl' => ['user/login']
        ],
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
        ],
        'assetManager' => [
            'linkAssets' => true,
        ],
        'request' => [
            'class' => Request::class,
            'cookieValidationKey' => 'secret',
        ],
    ],
]);
