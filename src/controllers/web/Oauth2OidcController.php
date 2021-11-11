<?php

namespace rhertogh\Yii2Oauth2Server\controllers\web;

use rhertogh\Yii2Oauth2Server\controllers\web\base\Oauth2BaseApiController;
use rhertogh\Yii2Oauth2Server\controllers\web\openidconnect\Oauth2OidcUserinfoAction;
use rhertogh\Yii2Oauth2Server\filters\auth\Oauth2HttpBearerAuth;
use rhertogh\Yii2Oauth2Server\interfaces\controllers\web\Oauth2OidcControllerInterface;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;

class Oauth2OidcController extends Oauth2BaseApiController implements Oauth2OidcControllerInterface
{
    /**
     * @inheritDoc
     */
    public function behaviors()
    {
        return ArrayHelper::merge(parent::behaviors(), [
            'verbFilter' => [
                'class' => VerbFilter::class,
                'actions' => [
                    static::ACTION_NAME_USERINFO => ['GET', 'POST'],
                ],
            ],
            'authenticator' => [
                'class' => Oauth2HttpBearerAuth::class,
            ],
            'accessControl' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
        ]);
    }

    /**
     * @inheritDoc
     */
    public function actions()
    {
        return [
            static::ACTION_NAME_USERINFO => Oauth2OidcUserinfoAction::class,
        ];
    }
}
