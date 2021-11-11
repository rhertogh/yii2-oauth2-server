<?php

namespace sample\modules\api\controllers;

use sample\modules\api\controllers\base\BaseController;
use Yii;

/**
 * Sample API Controller to show the Yii2-Oauth2-Server in action
 */
class UserController extends BaseController
{
    /**
     * Sample action that returns the current identity
     * @return \yii\web\IdentityInterface|null
     */
    public function actionMe()
    {
        return Yii::$app->user->identity;
    }
}
