<?php

namespace Yii2Oauth2ServerTests\_helpers;

use rhertogh\Yii2Oauth2Server\interfaces\components\openidconnect\user\Oauth2OidcUserComponentInterface;
use Yii;
use yii\web\User;

class TestUserComponent extends User implements
    Oauth2OidcUserComponentInterface
{
    # region Oauth2OidcUserComponentInterface
    /**
     * @inheritDoc
     */
    public function reauthenticationRequired($clientAuthorizationRequest)
    {
        return Yii::$app->response->redirect([
            'site/login',
            'reauthenticate' => true,
            'clientAuthorizationRequestId' => $clientAuthorizationRequest->getRequestId(),
        ]);
    }

    /**
     * @inheritDoc
     */
    public function accountSelectionRequired($clientAuthorizationRequest)
    {
        return Yii::$app->response->redirect([
            'site/account-selection',
            'clientAuthorizationRequestId' => $clientAuthorizationRequest->getRequestId(),
        ]);
    }
    # endregion
}
