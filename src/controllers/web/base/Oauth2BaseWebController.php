<?php

namespace rhertogh\Yii2Oauth2Server\controllers\web\base;

use rhertogh\Yii2Oauth2Server\Oauth2Module;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;

/**
 * @property Oauth2Module $module
 */
abstract class Oauth2BaseWebController extends Controller
{
    public function beforeAction($action)
    {
        if (!$this->module->validateTlsConnection()) {
            throw new ForbiddenHttpException('TLS connection is required.');
        }

        return parent::beforeAction($action);
    }
}
