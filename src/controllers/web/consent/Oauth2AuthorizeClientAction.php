<?php

namespace rhertogh\Yii2Oauth2Server\controllers\web\consent;

use rhertogh\Yii2Oauth2Server\controllers\web\Oauth2ConsentController;
use rhertogh\Yii2Oauth2Server\exceptions\Oauth2ServerHttpException;
use Yii;
use yii\base\Action;
use yii\base\InvalidConfigException;
use yii\web\BadRequestHttpException;
use yii\web\HttpException;
use yii\web\ServerErrorHttpException;

/**
 * @property Oauth2ConsentController $controller
 */
class Oauth2AuthorizeClientAction extends Action
{
    public $clientAuthorizationView = null;

    public function init()
    {
        parent::init();
        if (empty($this->clientAuthorizationView)) {
            throw new InvalidConfigException('$clientAuthorizationView must be set.');
        }
    }

    public function run($clientAuthorizationRequestId)
    {
        try {
            $module = $this->controller->module;

            $clientAuthorizationRequest = $module->getClientAuthReqSession($clientAuthorizationRequestId);

            if (empty($clientAuthorizationRequest)) {
                throw new BadRequestHttpException(Yii::t('oauth2', 'Invalid clientAuthorizationRequestId.'));
            }

            if (
                $clientAuthorizationRequest->load(Yii::$app->request->post())
                && $clientAuthorizationRequest->validate()
            ) {
                return $module->generateClientAuthReqCompledRedirectResponse($clientAuthorizationRequest);
            }

            return $this->controller->render($this->clientAuthorizationView, [
                'clientAuthorizationRequest' => $clientAuthorizationRequest,
            ]);
        } catch (\Exception $e) {
            $message = Yii::t('oauth2', 'Unable to respond to client authorization request.');
            if ($e instanceof HttpException) {
                $message .= ' ' . $e->getMessage();
            }
            throw new ServerErrorHttpException($message, 0, $e);
        }
    }
}
