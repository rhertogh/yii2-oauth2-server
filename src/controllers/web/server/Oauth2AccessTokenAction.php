<?php

namespace rhertogh\Yii2Oauth2Server\controllers\web\server;

use rhertogh\Yii2Oauth2Server\controllers\web\Oauth2ServerController;
use rhertogh\Yii2Oauth2Server\controllers\web\server\base\Oauth2BaseServerAction;
use rhertogh\Yii2Oauth2Server\helpers\Psr7Helper;
use rhertogh\Yii2Oauth2Server\interfaces\controllers\web\server\Oauth2AccessTokenActionInterface;
use Yii;
use yii\web\HttpException;

/**
 * @property Oauth2ServerController $controller
 */
class Oauth2AccessTokenAction extends Oauth2BaseServerAction implements Oauth2AccessTokenActionInterface
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
            return $this->processException($e, __METHOD__);
        }
    }
}
