<?php

namespace Yii2Oauth2ServerTests\_helpers\controllers\web;

use rhertogh\Yii2Oauth2Server\filters\auth\Oauth2HttpBearerAuth;
use Yii;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
use yii\web\Response;

class TestApiController extends Controller
{
    public function behaviors()
    {
        return ArrayHelper::merge(parent::behaviors(), [
            'authenticator' => [
                'class' => Oauth2HttpBearerAuth::class,
            ],
        ]);
    }

    public function beforeAction($action)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        return parent::beforeAction($action);
    }

    public function actionMe()
    {
        return Yii::$app->user->identity;
    }
}
