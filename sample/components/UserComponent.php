<?php

namespace sample\components;

use rhertogh\Yii2Oauth2Server\components\server\grants\Oauth2PasswordGrant;
use rhertogh\Yii2Oauth2Server\interfaces\components\openidconnect\user\Oauth2OidcUserComponentInterface;
use rhertogh\Yii2Oauth2Server\interfaces\components\user\Oauth2PasswordGrantUserComponentInterface;
use Yii;
use yii\db\Exception;
use yii\web\IdentityInterface;
use yii\web\User;

// phpcs:disable Generic.Files.LineLength.TooLong -- Sample documentation
class UserComponent extends User implements
    Oauth2OidcUserComponentInterface, # Optional interface, only required when 'Open ID Connect' is used.
    Oauth2PasswordGrantUserComponentInterface # Optional interface, only needed if you want to use  the Password Grant before/after login methods.
{
    // phpcs:enable Generic.Files.LineLength.TooLong

    # region Oauth2OidcUserComponentInterface
    /**
     * @inheritDoc
     */
    public function reauthenticationRequired($clientAuthorizationRequest)
    {
        return Yii::$app->response->redirect([
            'user/login',
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
            'user/select-account',
            'clientAuthorizationRequestId' => $clientAuthorizationRequest->getRequestId(),
        ]);
    }
    # endregion Oauth2OidcUserComponentInterface

    # region Updates User's `latest_authenticated_at`
    # which is used for Oauth2OidcUserInterface::getLatestAuthenticatedAt()
    /**
     * @inheritDoc
     * @param \sample\models\User $identity
     */
    protected function afterLogin($identity, $cookieBased, $duration)
    {
        parent::afterLogin($identity, $cookieBased, $duration);

        $identity->latest_authenticated_at = time();
        if (!$identity->save()) {
            throw new Exception('Could not save user.');
        }
    }
    # endregion

    # region Oauth2PasswordGrantUserComponentInterface
    /**
     * @inheritDoc
     */
    public function beforeOauth2PasswordGrantLogin($identity, $client, $grant)
    {
        // Just always allow access in the sample app,
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
