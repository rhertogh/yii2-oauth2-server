<?php

namespace rhertogh\Yii2Oauth2Server\controllers\web;

use rhertogh\Yii2Oauth2Server\controllers\web\base\Oauth2BaseApiController;
use rhertogh\Yii2Oauth2Server\interfaces\controllers\web\server\Oauth2AccessTokenActionInterface;
use rhertogh\Yii2Oauth2Server\interfaces\controllers\web\server\Oauth2AuthorizeActionInterface;
use rhertogh\Yii2Oauth2Server\interfaces\controllers\web\server\Oauth2RevokeActionInterface;
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
            static::ACTION_NAME_ACCESS_TOKEN => Oauth2AccessTokenActionInterface::class,
            static::ACTION_NAME_AUTHORIZE => Oauth2AuthorizeActionInterface::class,
            static::ACTION_NAME_REVOKE => Oauth2RevokeActionInterface::class,
        ];
    }
}
