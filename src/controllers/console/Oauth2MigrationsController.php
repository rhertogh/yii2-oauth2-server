<?php

namespace rhertogh\Yii2Oauth2Server\controllers\console;

use rhertogh\Yii2Oauth2Server\controllers\console\base\Oauth2BaseConsoleController;
use rhertogh\Yii2Oauth2Server\controllers\console\migrations\Oauth2GenerateMigrationsAction;
use yii\helpers\ArrayHelper;

class Oauth2MigrationsController extends Oauth2BaseConsoleController
{
    public $force = false;

    /**
     * @inheritDoc
     */
    public function options($actionID)
    {
        return ArrayHelper::merge(parent::options($actionID), [
            'force',
        ]);
    }

    /**
     * @inheritDoc
     */
    public function optionAliases()
    {
        return ArrayHelper::merge(parent::optionAliases(), [
            'f' => 'force',
        ]);
    }

    /**
     * @inheritDoc
     */
    public function actions()
    {
        return [
            'generate' => Oauth2GenerateMigrationsAction::class,
        ];
    }
}
