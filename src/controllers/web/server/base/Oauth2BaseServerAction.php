<?php

namespace rhertogh\Yii2Oauth2Server\controllers\web\server\base;

use League\OAuth2\Server\Exception\OAuthServerException;
use rhertogh\Yii2Oauth2Server\controllers\web\base\Oauth2BaseWebController;
use rhertogh\Yii2Oauth2Server\exceptions\Oauth2ServerHttpException;
use rhertogh\Yii2Oauth2Server\helpers\UrlHelper;
use Yii;
use yii\base\Action;
use yii\base\ErrorException;
use yii\base\Exception;
use yii\base\UserException;
use yii\web\HttpException;
use yii\web\Response;

/**
 * @property Oauth2BaseWebController $controller
 */
abstract class Oauth2BaseServerAction extends Action
{
//    protected function processOAuthServerException(OAuthServerException $e)
//    {
//        if ($e->hasRedirect()) {
//            return $this->controller->redirect(UrlHelper::addQueryParams($e->getRedirectUri(), [
//                'error' => $e->getErrorType()
//            ]));
//        }
//        throw Oauth2ServerHttpException::createFromOAuthServerException($e);
//    }

    /**
     * @param \Exception $exception
     * @return Response
     * @see https://datatracker.ietf.org/doc/html/rfc6749#section-5.2
     */
    protected function processException($exception)
    {
        if (Yii::$app->has('response')) {
            $response = Yii::$app->getResponse();
            // reset parameters of response to avoid interference with partially created response data
            // in case the error occurred while sending the response.
            $response->isSent = false;
            $response->stream = null;
            $response->data = null;
            $response->content = null;
        } else {
            $response = new Response();
        }

        if ($exception instanceof OAuthServerException) {
            if ($exception->hasRedirect()) {
                return $this->controller->redirect(UrlHelper::addQueryParams($exception->getRedirectUri(), [
                    'error' => $exception->getErrorType()
                ]));
            }

            $response->setStatusCode($exception->getHttpStatusCode());

            $error = $exception->getErrorType();
            $hint = $exception->getHint();
            $description = $exception->getMessage() . ($hint ? ' ' . $hint : '');
        } else {
            $response->setStatusCodeByException($exception);

            $error = ($exception instanceof Exception || $exception instanceof ErrorException)
                ? $exception->getName()
                : 'Exception';

            $displayNonHttpExceptionMessages = $this->controller->module->displayConfidentialExceptionMessages !== null
                ? $this->controller->module->displayConfidentialExceptionMessages
                : YII_DEBUG;

            if (
                !$displayNonHttpExceptionMessages
                && !$exception instanceof UserException
                && !$exception instanceof HttpException
            ) {
                $description = Yii::t('yii', 'An internal server error occurred.');
            } else {
                $description = (string)$exception;
            }
        }

        $response->data = [
            'error' => $error,
            'error_description' => $description,
        ];

        return $response;
    }
}
