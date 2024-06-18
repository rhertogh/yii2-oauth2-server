<?php

namespace rhertogh\Yii2Oauth2Server\interfaces\components\authorization\EndSession;

use rhertogh\Yii2Oauth2Server\interfaces\components\authorization\base\Oauth2BaseAuthorizationRequestInterface;
use rhertogh\Yii2Oauth2Server\interfaces\components\authorization\client\Oauth2ClientScopeAuthorizationRequestInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\Oauth2ClientInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\Oauth2ScopeInterface;

interface Oauth2EndSessionAuthorizationRequestInterface extends Oauth2BaseAuthorizationRequestInterface
{
    /**
     * Get the OIDC id token.
     * @return string|null
     * @since 1.0.0
     */
    public function getIdTokenHint();

    /**
     * Set the OIDC id token.
     * @param string|null $idTokenHint
     * @return $this
     * @since 1.0.0
     */
    public function setIdTokenHint($idTokenHint);

    /**
     * Get the original OIDC End Session url.
     * @return string|null
     * @since 1.0.0
     */
    public function getEndSessionUrl();

    /**
     * Set the original OIDC End Session url.
     * @param string|null $endSessionUrl
     * @return $this
     * @since 1.0.0
     */
    public function setEndSessionUrl($endSessionUrl);


    /**
     * @return void
     */
    public function validateRequest();

    /**
     * @return bool
     */
    public function getEndUserAuthorizationRequired();

    /**
     * @param bool $endUserAuthorizationRequired
     * @return $this
     */
    public function setEndUserAuthorizationRequired($endUserAuthorizationRequired);

    /**
     * Get the url to the OIDC End Session request url including the End Session Authorization Request id.
     * This can be used to redirect the user agent back to after the user authorized the logout.
     * @return  string|null
     * @since 1.0.0
     */
    public function getEndSessionRequestUrl();

    /**
     * Mark the request as approved and process it.
     * Note: This should only be done if `getEndUserAuthorizationRequired()` is `false`.
     */
    public function autoApproveAndProcess();

    /**
     * Get the final redirect URL when the request is completed. This depends on the 'authorization status':
     *  - if "approved" (or $ignoreAuthorizationStatus is `true`):
     *    The `post_logout_redirect_uri` (or the default return url) with the `state` query parameter.
     *  - if "declined":
     *    The 'error return url'.
     * @param bool $ignoreAuthorizationStatus Always return the "approved" url.
     * @return string
     */
    public function getRequestCompletedRedirectUrl($ignoreAuthorizationStatus = false);
}
