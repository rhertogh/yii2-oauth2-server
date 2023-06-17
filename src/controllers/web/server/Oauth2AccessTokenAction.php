<?php

namespace rhertogh\Yii2Oauth2Server\controllers\web\server;

use rhertogh\Yii2Oauth2Server\controllers\web\Oauth2ServerController;
use rhertogh\Yii2Oauth2Server\controllers\web\server\base\Oauth2BaseServerAction;
use rhertogh\Yii2Oauth2Server\exceptions\Oauth2ServerHttpException;
use rhertogh\Yii2Oauth2Server\helpers\Psr7Helper;
use Yii;
use yii\web\HttpException;
use yii\web\Response;

/**
 * @property Oauth2ServerController $controller
 */
class Oauth2AccessTokenAction extends Oauth2BaseServerAction
{
    /**
     * @throws HttpException
     */
    public function run()
    {
        try {
            $server = $this->controller->module->getAuthorizationServer();
            $psr7Request = Psr7Helper::yiiToPsr7Request(Yii::$app->request);
            $psr7Response = Psr7Helper::yiiToPsr7Response(Yii::$app->response);
            $psr7Response = $server->respondToAccessTokenRequest($psr7Request, $psr7Response);
            return Psr7Helper::psr7ToYiiResponse($psr7Response);
        } catch (\Exception $e) {
            Yii::error((string)$e, __METHOD__);
            return $this->processException($e);
        }
    }
}
