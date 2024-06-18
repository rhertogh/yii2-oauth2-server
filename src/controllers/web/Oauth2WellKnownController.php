<?php

namespace rhertogh\Yii2Oauth2Server\controllers\web;

use rhertogh\Yii2Oauth2Server\controllers\web\base\Oauth2BaseApiController;
use rhertogh\Yii2Oauth2Server\interfaces\controllers\web\Oauth2WellKnownControllerInterface;
use rhertogh\Yii2Oauth2Server\interfaces\controllers\web\wellknown\Oauth2OpenidConfigurationActionInterface;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;

class Oauth2WellKnownController extends Oauth2BaseApiController implements Oauth2WellKnownControllerInterface
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
                    static::ACTION_NAME_OPENID_CONFIGURATION => ['GET'],
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
            static::ACTION_NAME_OPENID_CONFIGURATION => Oauth2OpenidConfigurationActionInterface::class,
        ];
    }
}
