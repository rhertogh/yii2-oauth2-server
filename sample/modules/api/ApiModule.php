<?php

namespace sample\modules\api;

use Yii;
use yii\base\Module;

/**
 * Sample API Module to show the Yii2-Oauth2-Server in action
 */
class ApiModule extends Module
{
    /**
     * @inheritDoc
     */
    public function init()
    {
        parent::init();

        // Only allow "stateless" access to the api, for more information see
        // https://www.yiiframework.com/doc/guide/2.0/en/rest-authentication#authentication.
        Yii::$app->user->enableSession = false;
    }
}
