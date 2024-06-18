<?php

namespace rhertogh\Yii2Oauth2Server\controllers\web;

use rhertogh\Yii2Oauth2Server\controllers\web\base\Oauth2BaseWebController;
use rhertogh\Yii2Oauth2Server\interfaces\controllers\web\consent\Oauth2AuthorizeClientActionInterface;
use rhertogh\Yii2Oauth2Server\controllers\web\consent\Oauth2AuthorizeEndSessionAction;
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
                    static::ACTION_NAME_AUTHORIZE_END_SESSION => ['GET', 'POST'],
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
                'class' => Oauth2AuthorizeClientActionInterface::class,
                'clientAuthorizationView' => $this->module->clientAuthorizationView,
            ],
            static::ACTION_NAME_AUTHORIZE_END_SESSION => [
                'class' => Oauth2AuthorizeEndSessionAction::class,
                'openIdConnectLogoutConfirmationView' => $this->module->openIdConnectLogoutConfirmationView,
            ],
        ];
    }
}
