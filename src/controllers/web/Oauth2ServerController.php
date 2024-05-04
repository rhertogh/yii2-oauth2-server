<?php

namespace rhertogh\Yii2Oauth2Server\controllers\web;

use rhertogh\Yii2Oauth2Server\controllers\web\base\Oauth2BaseApiController;
use rhertogh\Yii2Oauth2Server\controllers\web\server\Oauth2AccessTokenAction;
use rhertogh\Yii2Oauth2Server\controllers\web\server\Oauth2AuthorizeAction;
use rhertogh\Yii2Oauth2Server\controllers\web\server\Oauth2RevokeAction;
use rhertogh\Yii2Oauth2Server\interfaces\controllers\web\Oauth2ServerControllerInterface;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;

class Oauth2ServerController extends Oauth2BaseApiController implements Oauth2ServerControllerInterface
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
                    static::ACTION_NAME_ACCESS_TOKEN => ['POST'],
                    static::ACTION_NAME_AUTHORIZE => ['GET', 'POST'],
                    static::ACTION_NAME_REVOKE => ['POST'],
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
            static::ACTION_NAME_ACCESS_TOKEN => Oauth2AccessTokenAction::class,
            static::ACTION_NAME_AUTHORIZE => Oauth2AuthorizeAction::class,
            static::ACTION_NAME_REVOKE => Oauth2RevokeAction::class,
        ];
    }
}
