<?php

namespace rhertogh\Yii2Oauth2Server\controllers\console\base;

use rhertogh\Yii2Oauth2Server\Oauth2Module;
use yii\console\Controller;
use yii\helpers\ArrayHelper;

/**
 * @property Oauth2Module $module
 */
class Oauth2BaseConsoleController extends Controller
{
    /**
     * @var bool
     */
    public $verbose = false;

    public function options($actionID)
    {
        return ArrayHelper::merge(parent::options($actionID), [
            'verbose',
        ]);
    }

    public function optionAliases()
    {
        return ArrayHelper::merge(parent::optionAliases(), [
            'v' => 'verbose',
        ]);
    }
}
