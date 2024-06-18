<?php

namespace rhertogh\Yii2Oauth2Server\interfaces\components\authorization\base;

use rhertogh\Yii2Oauth2Server\interfaces\models\external\user\Oauth2UserInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\Oauth2ClientInterface;
use rhertogh\Yii2Oauth2Server\Oauth2Module;
use yii\base\Configurable;

interface Oauth2BaseAuthorizationRequestInterface extends Configurable
{
    /**
     * Authorization status denied: The user denied the Client Authorization Request
     * @since 1.0.0
     */
    public const AUTHORIZATION_DENIED = 'denied';
    /**
     * Authorization status approved: The user approved the Client Authorization Request,
     *
     * @since 1.0.0
     */
    public const AUTHORIZATION_APPROVED = 'approved';

    /**
     * Possible authorization statuses
     * @return string[]
     * @since 1.0.0
     */
    public static function getPossibleAuthorizationStatuses();

    /**
     * Serialization helper. E.g. for storing the Authorization Request in the session.
     * @return array
     * @since 1.0.0
     */
    public function __serialize();

    /**
     * Serialization helper. E.g. restoring the Authorization Request from the session.
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
     * Get the randomly generated request id,
     * @return string
     * @since 1.0.0
     */
    public function getRequestId();

    /**
     * Get the Oauth 2 request client identifier.
     * @return string|null
     * @see https://datatracker.ietf.org/doc/html/rfc6749#section-4.1.1
     * @since 1.0.0
     */
    public function getClientIdentifier();

    /**
     * Set the Oauth 2 request client identifier.
     * @param string|null $clientIdentifier
     * @return $this
     * @see https://datatracker.ietf.org/doc/html/rfc6749#section-4.1.1
     * @since 1.0.0
     */
    public function setClientIdentifier($clientIdentifier);

    /**
     * Get the Oauth2 Client.
     * @return Oauth2ClientInterface|null
     * @since 1.0.0
     */
    public function getClient();

    /**
     * Set the Oauth2 Client.
     * @param Oauth2ClientInterface|null $client
     * @return $this
     * @since 1.0.0
     */
    public function setClient($client);

    /**
     * Returns if the client is identifiable, this is the case if the client is confidential or the redirect uri starts.
     * with 'https://'.
     * @return bool
     * @see https://datatracker.ietf.org/doc/html/rfc6749#section-10.2
     * @since 1.0.0
     */
    public function isClientIdentifiable();

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
     * @param Oauth2UserInterface|null $userIdentity
     * @return $this
     * @since 1.0.0
     */
    public function setUserIdentity($userIdentity);

    /**
     * Get the Authorization Request redirect url.
     * @return  string|null
     * @see https://datatracker.ietf.org/doc/html/rfc6749#section-4.1.1
     * @since 1.0.0
     */
    public function getRedirectUri();

    /**
     * Set the Authorization Request redirect url.
     * @param string|null $redirectUri
     * @return $this
     * @see https://datatracker.ietf.org/doc/html/rfc6749#section-4.1.1
     * @since 1.0.0
     */
    public function setRedirectUri($redirectUri);

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
     * Performs the user input data validation.
     * @param string[]|null $attributeNames
     * @param bool $clearErrors
     * @return bool
     * @see \yii\base\Model::validate()
     * @since 1.0.0
     */
    public function validate($attributeNames = null, $clearErrors = true);

    /**
     * Check if the user identity is allowed to complete the authorization request.
     * This can be useful to restrict access to certain client/user combinations
     * @return bool
     */
    public function isAuthorizationAllowed();

    /**
     * Get the current Client Authorization Request status.
     * @return string|null
     * @see getPossibleAuthorizationStatuses()
     * @since 1.0.0
     */
    public function getAuthorizationStatus();

    /**
     * Set the current Client Authorization Request status.
     * @param string|null $authorizationStatus
     * @return $this
     * @see getPossibleAuthorizationStatuses()
     * @since 1.0.0
     */
    public function setAuthorizationStatus($authorizationStatus);

    /**
     * Process the Authorization Request.
     * @since 1.0.0
     */
    public function processAuthorization();

    /**
     * Returns if the Authorization Request "Authorization Status" is approved.
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
