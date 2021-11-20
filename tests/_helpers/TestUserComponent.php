<?php

namespace Yii2Oauth2ServerTests\_helpers;

use rhertogh\Yii2Oauth2Server\interfaces\components\openidconnect\user\Oauth2OidcUserComponentInterface;
use rhertogh\Yii2Oauth2Server\interfaces\components\user\Oauth2PasswordGrantUserComponentInterface;
use Yii;
use yii\web\User;

class TestUserComponent extends User implements
    Oauth2OidcUserComponentInterface,
    Oauth2PasswordGrantUserComponentInterface
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

    # region Oauth2PasswordGrantUserComponentInterface
    /**
     * @inheritDoc
     */
    public function beforeOauth2PasswordGrantLogin($identity, $client, $grant)
    {
        // Just always allow access in the test app,
        // for your application you might limit the access to certain clients.
        return true;
    }

    /**
     * @inheritDoc
     */
    public function afterOauth2PasswordGrantLogin($identity, $grant)
    {
        // You can implement your own logic here, for example to add an entry in a log.
    }
    # endregion
}
