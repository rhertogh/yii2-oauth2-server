<?php

namespace sample\components;

use rhertogh\Yii2Oauth2Server\interfaces\components\openidconnect\user\Oauth2OidcUserComponentInterface;
use Yii;
use yii\db\Exception;
use yii\web\User;

class UserComponent extends User implements Oauth2OidcUserComponentInterface
{
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
}
