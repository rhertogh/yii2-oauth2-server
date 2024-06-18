<?php

namespace rhertogh\Yii2Oauth2Server\controllers\web\consent;

use rhertogh\Yii2Oauth2Server\controllers\web\base\Oauth2BaseWebAction;
use rhertogh\Yii2Oauth2Server\controllers\web\Oauth2ConsentController;
use rhertogh\Yii2Oauth2Server\exceptions\Oauth2ServerHttpException;
use rhertogh\Yii2Oauth2Server\interfaces\controllers\web\consent\Oauth2AuthorizeEndSessionActionInterface;
use Yii;
use yii\base\InvalidConfigException;
use yii\web\BadRequestHttpException;
use yii\web\HttpException;
use yii\web\ServerErrorHttpException;

/**
 * @property Oauth2ConsentController $controller
 */
class Oauth2AuthorizeEndSessionAction extends Oauth2BaseWebAction implements Oauth2AuthorizeEndSessionActionInterface
{
    /**
     * Path to view file for End Session authorization.
     * @var string|null
     */
    public $openIdConnectLogoutConfirmationView = null;

    public function init()
    {
        parent::init();
        if (empty($this->openIdConnectLogoutConfirmationView)) {
            throw new InvalidConfigException('$openIdConnectLogoutConfirmationView must be set.');
        }
    }

    public function run($endSessionAuthorizationRequestId)
    {
        try {
            $module = $this->controller->module;

            $endSessionAuthorizationRequest = $module->getEndSessionAuthReqSession($endSessionAuthorizationRequestId);

            if (empty($endSessionAuthorizationRequest)) {
                throw new BadRequestHttpException(Yii::t('oauth2', 'Invalid endSessionAuthorizationRequestId.'));
            }

            if (
                $endSessionAuthorizationRequest->load(Yii::$app->request->post())
                && $endSessionAuthorizationRequest->validate()
            ) {
                return $module->generateEndSessionAuthReqCompledRedirectResponse($endSessionAuthorizationRequest);
            }

            return $this->controller->render($this->openIdConnectLogoutConfirmationView, [
                'endSessionAuthorizationRequest' => $endSessionAuthorizationRequest,
            ]);
        } catch (\Exception $e) {
            $message = Yii::t('oauth2', 'Unable to respond to logout authorization request.');
            if ($e instanceof HttpException) {
                $message .= ' ' . $e->getMessage();
            }
            throw new ServerErrorHttpException($message, 0, $e);
        }
    }
}
