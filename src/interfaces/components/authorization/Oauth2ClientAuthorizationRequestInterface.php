<?php

namespace rhertogh\Yii2Oauth2Server\interfaces\components\authorization;

use rhertogh\Yii2Oauth2Server\interfaces\models\external\user\Oauth2UserInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\Oauth2ClientInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\Oauth2ScopeInterface;
use rhertogh\Yii2Oauth2Server\Oauth2Module;
use yii\base\Configurable;

interface Oauth2ClientAuthorizationRequestInterface extends Configurable
{
    /**
     * Authorization status denied: The user denied the Client Authorization Request
     * @since 1.0.0
     */
    public const AUTHORIZATION_DENIED = 'denied';
    /**
     * Authorization status approved: The user approved the Client Authorization Request,
     * note that when the request included optional scopes the user might not have accepted all of them.
     * @since 1.0.0
     */
    public const AUTHORIZATION_APPROVED = 'approved';

    /**
     * Possible authorization statuses
     * @since 1.0.0
     */
    public const AUTHORIZATION_STATUSES = [
        self::AUTHORIZATION_DENIED,
        self::AUTHORIZATION_APPROVED,
    ];


    /**
     * Serialization helper. E.g. for storing the Client Authorization Request in the session.
     * @return array
     * @since 1.0.0
     */
    public function __serialize();

    /**
     * Serialization helper. E.g. restoring the Client Authorization Request from the session.
     * @param array $data
     * @since 1.0.0
     */
    public function __unserialize($data);

    /**
     * Get the module.
     * @return Oauth2Module
     * @since 1.0.0
     */
    public function getModule();

    /**
     * Set the module.
     * @param Oauth2Module $module
     * @return $this
     * @since 1.0.0
     */
    public function setModule($module);

    /**
     * Get the current Client Authorization Request status.
     * @return string|null
     * @see AUTHORIZATION_STATUSES
     * @since 1.0.0
     */
    public function getAuthorizationStatus();

    /**
     * Set the current Client Authorization Request status.
     * @param string|null $authorizationStatus
     * @return $this
     * @see AUTHORIZATION_STATUSES
     * @since 1.0.0
     */
    public function setAuthorizationStatus($authorizationStatus);

    /**
     * Get the randomly generated request id,
     * @return string
     * @since 1.0.0
     */
    public function getRequestId();

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
     * Get the Oauth 2 request client identifier.
     * @return int|null
     * @see https://datatracker.ietf.org/doc/html/rfc6749#section-4.1.1
     * @since 1.0.0
     */
    public function getClientIdentifier();

    /**
     * Set the Oauth 2 request client identifier.
     * @param int|null $clientIdentifier
     * @return $this
     * @see https://datatracker.ietf.org/doc/html/rfc6749#section-4.1.1
     * @since 1.0.0
     */
    public function setClientIdentifier($clientIdentifier);

    /**
     * Get the user identity for the Client Authorization Request. Note: this can differ from the current user
     * identity, for example when user account selection is supported for OpenID Connect.
     * @return Oauth2UserInterface|null
     * @since 1.0.0
     */
    public function getUserIdentity();

    /**
     * Set the user identity for the Client Authorization Request. Note: this can differ from the current user
     * identity, for example when user account selection is supported for OpenID Connect.
     * @param Oauth2UserInterface $userIdentity
     * @return $this
     * @since 1.0.0
     */
    public function setUserIdentity($userIdentity);

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
     * Get the url to the Oauth 2 authorization request url including the Client Authorization Request id.
     * This can be used to redirect the user agent back to after the user authorized the client.
     * @return  string|null
     * @since 1.0.0
     */
    public function getAuthorizationRequestUrl();

    /**
     * Get the Oauth 2 request redirect url.
     * @return  string|null
     * @see https://datatracker.ietf.org/doc/html/rfc6749#section-4.1.1
     * @since 1.0.0
     */
    public function getRedirectUri();

    /**
     * Set the Oauth 2 request redirect url.
     * @param string|null $redirectUri
     * @return $this
     * @see https://datatracker.ietf.org/doc/html/rfc6749#section-4.1.1
     * @since 1.0.0
     */
    public function setRedirectUri($redirectUri);

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
     * Get the Oauth2 Client.
     * @return Oauth2ClientInterface
     * @since 1.0.0
     */
    public function getClient();

    /**
     * Set the Oauth2 Client.
     * @param Oauth2ClientInterface $client
     * @return $this
     * @since 1.0.0
     */
    public function setClient($client);

    /**
     * Returns Scope Authorization Requests for all scopes that require approval by the user. This includes scopes
     * requested by the client or are set as default scopes and excludes previously authorized scopes by the user
     * for this client.
     * @return Oauth2ScopeAuthorizationRequestInterface[]
     * @since 1.0.0
     */
    public function getApprovalPendingScopes();

    /**
     * Returns Scope Authorization Requests for all scopes have been authorized before by the user for this client.
     * @return Oauth2ScopeAuthorizationRequestInterface[]
     * @since 1.0.0
     */
    public function getPreviouslyApprovedScopes();

    /**
     * Returns Scope Authorization Requests for all scopes have are  for this client.
     * @return Oauth2ScopeInterface[]
     * @since 1.0.0
     */
    public function getScopesAppliedByDefaultAutomatically();

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
     * Returns if the client is identifiable, this is the case if the client is confidential or the redirect uri starts.
     * with 'https://'.
     * @return bool
     * @see https://datatracker.ietf.org/doc/html/rfc6749#section-10.2
     * @since 1.0.0
     */
    public function isClientIdentifiable();

    /**
     * Performs the user input data validation.
     * @param null $attributeNames
     * @param bool $clearErrors
     * @return bool
     * @see \yii\base\Model::validate()
     * @since 1.0.0
     */
    public function validate($attributeNames = null, $clearErrors = true);

    /**
     * Populates the model with user input data.
     * @param array $data
     * @param string|null $formName
     * @return bool
     * @see \yii\base\Model::load()
     * @since 1.0.0
     */
    public function load($data, $formName = null);

    /**
     * Check if the user identity is allowed to complete the authorization request.
     * This can be useful to restrict access to certain client/user combinations
     * @return bool
     */
    public function isAuthorizationAllowed();
    
    /**
     * Process the Client Authorization Request. This method persists the authorized client and scopes.
     * @since 1.0.0
     */
    public function processAuthorization();

    /**
     * Returns if the Client Authorization Request "Authorization Status" is approved.
     * @return bool
     * @see getAuthorizationStatus(), AUTHORIZATION_APPROVED
     * @since 1.0.0
     */
    public function isApproved();

    /**
     * Returns if the Client Authorization Request is completed, this is the case if the request has successfully been
     * processed.
     * @return bool
     * @see processAuthorization()
     * @since 1.0.0
     */
    public function isCompleted();
}
