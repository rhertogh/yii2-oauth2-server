<?php

namespace rhertogh\Yii2Oauth2Server\controllers\console;

use rhertogh\Yii2Oauth2Server\controllers\console\base\Oauth2BaseConsoleController;
use rhertogh\Yii2Oauth2Server\controllers\console\debug\Oauth2DebugConfigActionInterface;
use rhertogh\Yii2Oauth2Server\interfaces\controllers\console\Oauth2DebugControllerInterface;

class Oauth2DebugController extends Oauth2BaseConsoleController implements Oauth2DebugControllerInterface
{
    /**
     * @inheritDoc
     */
    public $defaultAction = 'config';

    /**
     * @inheritDoc
     */
    public function actions()
    {
        return [
            'config' => Oauth2DebugConfigActionInterface::class,
        ];
    }
}
