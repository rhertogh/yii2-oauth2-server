<?php

namespace rhertogh\Yii2Oauth2Server\interfaces\components\openidconnect\user;

use rhertogh\Yii2Oauth2Server\interfaces\components\authorization\client\Oauth2ClientAuthorizationRequestInterface;
use yii\web\Response;

interface Oauth2OidcUserComponentInterface
{
    /**
     * Redirects the user agent to the login page.
     * The user MUST authenticate themselves even if they are already logged in.
     *
     * After the reauthentication is complete $clientAuthorizationRequest->setUserAuthenticatedDuringRequest(true) must
     * be called and the user agent should be redirected to the authorization request (can be retrieved with
     * $clientAuthorizationRequest->getAuthorizationRequestUrl()).
     * @param Oauth2ClientAuthorizationRequestInterface $clientAuthorizationRequest
     * @return Response
     * @since 1.0.0
     */
    public function reauthenticationRequired($clientAuthorizationRequest);

    /**
     * Redirects the user agent to the user account selection page, or return `false` in case account selection is not
     * supported.
     *
     * After the user account selection is complete $clientAuthorizationRequest->setUserIdentity($userIdentity) must
     * be called and the user agent should be redirected to the authorization request (can be retrieved with
     * $clientAuthorizationRequest->getAuthorizationRequestUrl()).
     * @param Oauth2ClientAuthorizationRequestInterface $clientAuthorizationRequest
     * @return Response|false
     * @since 1.0.0
     */
    public function accountSelectionRequired($clientAuthorizationRequest);
}
