<?php

namespace rhertogh\Yii2Oauth2Server\controllers\web\base;

use rhertogh\Yii2Oauth2Server\Oauth2Module;
use Yii;
use yii\filters\ContentNegotiator;
use yii\filters\Cors;
use yii\web\Controller;
use yii\web\Response;

abstract class Oauth2BaseApiController extends Oauth2BaseWebController
{
    /**
     * @inheritDoc
     */
    public $enableCsrfValidation = false;

    /**
     * @inheritDoc
     */
    public function behaviors()
    {
        return [
            'cors' => [
                'class' => Cors::class,
            ],
            'contentNegotiator' => [
                'class' => ContentNegotiator::class,
                'formats' => [
                    'application/json' => Response::FORMAT_JSON,
                ],
            ],
        ];
    }
}
