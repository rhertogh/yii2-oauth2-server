<?php

namespace sample\modules\api\controllers\base;

use rhertogh\Yii2Oauth2Server\filters\auth\Oauth2HttpBearerAuth;
use yii\helpers\ArrayHelper;
use yii\rest\Controller;

/**
 * Sample API Base Controller to show the Yii2-Oauth2-Server in action
 */
abstract class BaseController extends Controller
{
    /**
     * Sets the Oauth2HttpBearerAuth as authentication filter,
     * this will log in the user based on the bearer access token.
     *
     * @inheritDoc
     */
    public function behaviors()
    {
        return ArrayHelper::merge(parent::behaviors(), [
            'authenticator' => [
                // Use Oauth2HttpBearerAuth. To support multiple authentication methods please see:
                // https://www.yiiframework.com/doc/guide/2.0/en/rest-authentication#authentication
                'class' => Oauth2HttpBearerAuth::class,
            ],
        ]);
    }
}
