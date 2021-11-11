<?php
/// WARNING! This configuration is optimized for local development and should not be used in any other environment (for both security and performance) ///

use rhertogh\Yii2Oauth2Server\models\base\Oauth2BaseActiveRecord;
use sample\dev\giiant\generators\model\Generator as ModelGenerator;
use yii\console\controllers\MigrateController;
use yii\helpers\ArrayHelper;

return ArrayHelper::merge(require('main.php'), [
    'bootstrap' => [
        'gii',
    ],
    'modules' => [
        'gii' => [
            'class' => yii\gii\Module::class,
            'generators' => [
                'giiant-model' => [
                    'class' => ModelGenerator::class,
                    'templates' => [
                        'Yii2Oauth2Server' => '@sample/dev/giiant/generators/model/templates',
                    ],
                ],
            ],
        ],
    ],
    'controllerMap' => [
        'migrate' => [
            'class' => MigrateController::class,
            'migrationPath' => [
                '@sample/migrations',
            ],
            'migrationNamespaces' => [
                'sample\\migrations\\oauth2', // Add the `Oauth2Module::$migrationsNamespace` to your Migration Controller
            ],
            'newFileOwnership' => '1000:1000', # Default WSL user id
            'newFileMode' => 0660,
        ],
        'batch' => [
            'class' => schmunk42\giiant\commands\BatchController::class,
            'template' => 'Yii2Oauth2Server',
            'overwrite' => true,
            'extendedModels' => false,
            'modelBaseClass' => Oauth2BaseActiveRecord::class,
            'modelNamespace' => 'rhertogh\\Yii2Oauth2Server\\models',
            'modelGenerateRelations' => ModelGenerator::RELATIONS_ALL_INVERSE,
            'modelGenerateJunctionRelationMode' => ModelGenerator::JUNCTION_RELATION_VIA_MODEL,
            'modelGenerateQuery' => true,
            'modelQueryNamespace' => 'rhertogh\\Yii2Oauth2Server\\models\\queries',
            'enableI18N' => true,
            'modelMessageCategory' => 'oauth2',
            'modelGenerateHintsFromComments' => false,
            'singularEntities' => false,
            'useBlameableBehavior' => false,
            'useTimestampBehavior' => false,
            'useTranslatableBehavior' => false,
            'tables' => [
                'oauth2_access_token',
                'oauth2_access_token_scope',
                'oauth2_auth_code',
                'oauth2_auth_code_scope',
                'oauth2_client',
                'oauth2_client_scope',
                'oauth2_refresh_token',
                'oauth2_scope',
                'oauth2_user_client',
                'oauth2_user_client_scope',
            ],
        ],
    ],
]);
