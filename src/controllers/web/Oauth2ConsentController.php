<?php

namespace rhertogh\Yii2Oauth2Server\controllers\web;

use rhertogh\Yii2Oauth2Server\controllers\web\base\Oauth2BaseWebController;
use rhertogh\Yii2Oauth2Server\controllers\web\consent\Oauth2AuthorizeClientAction;
use rhertogh\Yii2Oauth2Server\interfaces\controllers\web\Oauth2ConsentControllerInterface;
use yii\filters\VerbFilter;

class Oauth2ConsentController extends Oauth2BaseWebController implements Oauth2ConsentControllerInterface
{
    /**
     * @inheritDoc
     */
    public function behaviors()
    {
        return [
            'verbFilter' => [
                'class' => VerbFilter::class,
                'actions' => [
                    static::ACTION_NAME_AUTHORIZE_CLIENT => ['GET', 'POST'],
                ],
            ],
        ];
    }

    /**
     * @inheritDoc
     */
    public function actions()
    {
        return [
            static::ACTION_NAME_AUTHORIZE_CLIENT => [
                'class' => Oauth2AuthorizeClientAction::class,
                'clientAuthorizationView' => $this->module->clientAuthorizationView,
            ],
        ];
    }
}
