<?php

namespace rhertogh\Yii2Oauth2Server\controllers\web;

use rhertogh\Yii2Oauth2Server\controllers\web\base\Oauth2BaseApiController;
use rhertogh\Yii2Oauth2Server\controllers\web\certificates\Oauth2JwksAction;
use rhertogh\Yii2Oauth2Server\interfaces\controllers\web\Oauth2CertificatesControllerInterface;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;

class Oauth2CertificatesController extends Oauth2BaseApiController implements Oauth2CertificatesControllerInterface
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
                    static::ACTION_NAME_JWKS => ['GET'],
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
            static::ACTION_NAME_JWKS => Oauth2JwksAction::class,
        ];
    }
}
