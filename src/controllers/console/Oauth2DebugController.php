<?php

namespace rhertogh\Yii2Oauth2Server\controllers\console;

use rhertogh\Yii2Oauth2Server\controllers\console\base\Oauth2BaseConsoleController;
use rhertogh\Yii2Oauth2Server\controllers\console\debug\Oauth2DebugConfigAction;

class Oauth2DebugController extends Oauth2BaseConsoleController
{
    public $defaultAction = 'config';
    /**
     * @inheritDoc
     */
    public function actions()
    {
        return [
            'config' => Oauth2DebugConfigAction::class,
        ];
    }
}
