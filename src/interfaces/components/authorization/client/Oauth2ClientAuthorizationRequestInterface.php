<?php

namespace rhertogh\Yii2Oauth2Server\interfaces\components\authorization\client;

use rhertogh\Yii2Oauth2Server\interfaces\components\authorization\base\Oauth2BaseAuthorizationRequestInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\Oauth2ClientInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\Oauth2ScopeInterface;

/**
 *
 * Note: When the user has accepted the request but the request included optional scopes,
 * the user might not have accepted all of them.
 */
interface Oauth2ClientAuthorizationRequestInterface extends Oauth2BaseAuthorizationRequestInterface
{
    # region Oauth2BaseAuthorizationRequestInterface methods (overwritten for type covariance / PhpDoc)
    /**
     * @inheritDoc
     * @return Oauth2ClientInterface
     */
    public function getClient();

    /**
     * Get the Oauth 2 request redirect url.
     * @inheritDoc
     * @see https://datatracker.ietf.org/doc/html/rfc6749#section-4.1.1
     */
    public function getRedirectUri();

    /**
     * Set the Oauth 2 request redirect url.
     * @inheritDoc
     * @see https://datatracker.ietf.org/doc/html/rfc6749#section-4.1.1
     */
    public function setRedirectUri($redirectUri);
    # endregion

    /**
     * Get the original Oauth 2 authorization request url.
     * @return string|null
     * @since 1.0.0
     */
    public function getAuthorizeUrl();

    /**
     * Set the original Oauth 2 authorization request url.
     * @param string|null $authorizeUrl
     * @return $this
     * @since 1.0.0
     */
    public function setAuthorizeUrl($authorizeUrl);

    /**
     * Get the Oauth 2 request state.
     * @return string
     * @see https://datatracker.ietf.org/doc/html/rfc6749#section-4.1.1
     * @since 1.0.0
     */
    public function getState();

    /**
     * Set the Oauth 2 request state.
     * @param string $state
     * @return $this
     * @see https://datatracker.ietf.org/doc/html/rfc6749#section-4.1.1
     * @since 1.0.0
     */
    public function setState($state);

    /**
     * Get the Oauth 2 request grant type.
     * @return string|null
     * @see https://datatracker.ietf.org/doc/html/rfc6749#section-4.1.1
     * @since 1.0.0
     */
    public function getGrantType();

    /**
     * Set the Oauth 2 request grant type.
     * @param string|null $grantType
     * @see https://datatracker.ietf.org/doc/html/rfc6749#section-4.1.1
     * @return $this
     * @since 1.0.0
     */
    public function setGrantType($grantType);

    /**
     * Get the authorization request prompts (parsed from the "promp" parameter). Originally defined in the
     * OpenID Connect specs but generalized to take in consideration for all Oauth 2 authorization requests.
     * @return string[]
     * @see https://openid.net/specs/openid-connect-core-1_0.html#AuthRequest
     * @since 1.0.0
     */
    public function getPrompts();

    /**
    Set the authorization request prompts (parsed from the "promp" parameter). Originally defined in the
     * OpenID Connect specs but generalized to take in consideration for all Oauth 2 authorization requests.
     * @param string[] $prompts
     * @return $this
     * @see https://openid.net/specs/openid-connect-core-1_0.html#AuthRequest
     * @since 1.0.0
     */
    public function setPrompts($prompts);

    /**
     * Get the authorization request max age. Originally defined in the OpenID Connect specs but generalized
     * to take in consideration for all Oauth 2 authorization requests.
     * @return int|null
     * @see https://openid.net/specs/openid-connect-core-1_0.html#AuthRequest
     * @since 1.0.0
     */
    public function getMaxAge();

    /**
     * Set the authorization request max age. Originally defined in the OpenID Connect specs but generalized
     * to take in consideration for all Oauth 2 authorization requests.
     * @param int|null $maxAge
     * @return $this
     * @see https://openid.net/specs/openid-connect-core-1_0.html#AuthRequest
     * @since 1.0.0
     */
    public function setMaxAge($maxAge);

    /**
     * Get the Oauth 2 request scope identifiers.
     * @return string[]
     * @see https://datatracker.ietf.org/doc/html/rfc6749#section-4.1.1
     * @since 1.0.0
     */
    public function getRequestedScopeIdentifiers();

    /**
     * Set the Oauth 2 request scope identifiers.
     * @param string[] $requestedScopeIdentifiers
     * @return $this
     * @see https://datatracker.ietf.org/doc/html/rfc6749#section-4.1.1
     * @since 1.0.0
     */
    public function setRequestedScopeIdentifiers($requestedScopeIdentifiers);

    /**
     * Get the selected scopes by the user during the client and scope authorization.
     * @return string[]
     * @since 1.0.0
     */
    public function getSelectedScopeIdentifiers();

    /**
     * Set the selected scopes by the user during the client and scope authorization.
     * @param string[] $selectedScopeIdentifiers
     * @return $this
     * @since 1.0.0
     */
    public function setSelectedScopeIdentifiers($selectedScopeIdentifiers);

    /**
     * Set authentication status when the Client Authorization Request starts. This helps to determine if the user
     * might need to be reauthenticated (for example for OpenID Connect requests).
     * @param bool $authenticatedBeforeRequest
     * @return $this
     * @since 1.0.0
     */
    public function setUserAuthenticatedBeforeRequest($authenticatedBeforeRequest);

    /**
     * Returns if the user was authentication when the Client Authorization Request started. This helps to determine
     * if the user might need to be reauthenticated (for example for OpenID Connect requests).
     * @return bool
     * @since 1.0.0
     */
    public function wasUserAuthenticatedBeforeRequest();

    /**
     * Set if the user is authentication during the Client Authorization Request. This helps to determine if the user
     * is (or might need to be) reauthenticated (for example for OpenID Connect requests).
     * @param bool $authenticatedDuringRequest
     * @return $this
     * @since 1.0.0
     */
    public function setUserAuthenticatedDuringRequest($authenticatedDuringRequest);

    /**
     * Returns if the user was authentication during the Client Authorization Request. This helps to determine if the
     * user is (or might need to be) reauthenticated (for example for OpenID Connect requests).
     * @return bool
     * @since 1.0.0
     */
    public function wasUserAthenticatedDuringRequest();

    /**
     * Returns Scope Authorization Requests for all scopes that require approval by the user. This includes scopes
     * requested by the client or are set as default scopes and excludes previously authorized scopes by the user
     * for this client.
     * @return Oauth2ClientScopeAuthorizationRequestInterface[]
     * @since 1.0.0
     */
    public function getApprovalPendingScopes();

    /**
     * Returns Scope Authorization Requests for all scopes that have been authorized before by the user for this client.
     * @return Oauth2ClientScopeAuthorizationRequestInterface[]
     * @since 1.0.0
     */
    public function getPreviouslyApprovedScopes();

    /**
     * Returns all Scopes that are applied by default without user authorization for this request.
     * This can be defined by the Scope or the ClientScope (where the latter has precedence).
     * @return Oauth2ScopeInterface[]
     * @since 1.0.0
     * @see Oauth2ScopeInterface::getAppliedByDefault()
     * @see Oauth2ClientScopeInterface::getAppliedByDefault()
     */
    public function getScopesAppliedByDefaultWithoutConfirm();

    /**
     * Returns if client/scope authorization is needed by the user.
     * @return bool
     * @since 1.0.0
     */
    public function isAuthorizationNeeded();

    /**
     * Returns if client authorization is needed by the user.
     * @return bool
     * @since 1.0.0
     */
    public function isClientAuthorizationNeeded();

    /**
     * Returns if scope authorization is needed by the user.
     * @return bool
     * @since 1.0.0
     */
    public function isScopeAuthorizationNeeded();

    /**
     * Process the Client Authorization Request. This method persists the authorized client and scopes.
     * @inheritDoc
     */
    public function processAuthorization();

    /**
     * Get the url to the Oauth 2 authorization request url including the Client Authorization Request id.
     * This can be used to redirect the user agent back to after the user authorized the client.
     * @return  string|null
     * @since 1.0.0
     */
    public function getAuthorizationRequestUrl();
}
