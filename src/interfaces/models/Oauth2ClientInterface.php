<?php

namespace rhertogh\Yii2Oauth2Server\interfaces\models;

use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Repositories\ScopeRepositoryInterface;
use rhertogh\Yii2Oauth2Server\interfaces\components\encryption\Oauth2CryptographerInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\base\Oauth2ActiveRecordInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\base\Oauth2EnabledInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\base\Oauth2EncryptedStorageInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\base\Oauth2IdentifierInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\queries\Oauth2ClientQueryInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\queries\Oauth2ClientScopeQueryInterface;
use rhertogh\Yii2Oauth2Server\Oauth2Module;

interface Oauth2ClientInterface extends
    Oauth2ActiveRecordInterface,
    Oauth2IdentifierInterface,
    Oauth2EnabledInterface,
    Oauth2EncryptedStorageInterface,
    ClientEntityInterface
{
    /**
     * Client Type "Confidential": Identifies the client via a shared secret.
     * Note: This should only be trusted in case the client can store the secret securely, e.g. another server.
     * @since 1.0.0
     * @see https://oauth.net/2/client-types/
     */
    public const TYPE_CONFIDENTIAL = 1;
    /**
     * Client Type "Public": In case the client can not store a secret securely it should be declared public,
     * e.g. web- or mobile applications.
     * @since 1.0.0
     * @see https://oauth.net/2/client-types/
     */
    public const TYPE_PUBLIC = 2;
    /**
     * Client Types
     * @since 1.0.0
     * @see https://oauth.net/2/client-types/
     */
    public const TYPES = [
        self::TYPE_CONFIDENTIAL,
        self::TYPE_PUBLIC,
    ];

    public const OIDC_RP_INITIATED_LOGOUT_DISABLED = 0;
    public const OIDC_RP_INITIATED_LOGOUT_ENABLED = 1;
    public const OIDC_RP_INITIATED_LOGOUT_ENABLED_WITHOUT_VERIFICATION = 2;

    public const  OIDC_RP_INITIATED_LOGOUT_OPTIONS = [
        self::OIDC_RP_INITIATED_LOGOUT_DISABLED,
        self::OIDC_RP_INITIATED_LOGOUT_ENABLED,
        self::OIDC_RP_INITIATED_LOGOUT_ENABLED_WITHOUT_VERIFICATION,
    ];

    /**
     * @inheritDoc
     * @return Oauth2ClientQueryInterface
     */
    public static function find();

    /**
     * Get the Oauth2Module
     * @return Oauth2Module
     * @since 1.0.0
     */
    public function getModule();

    /**
     * Set the Oauth2Module
     * @param Oauth2Module $module
     * @return $this
     * @since 1.0.0
     */
    public function setModule($module);

    /**
     * Set the client name
     * @param string $name
     * @return $this
     * @since 1.0.0
     */
    public function setName($name);

    /**
     * Get the client type
     * @return int
     * @since 1.0.0
     * @see self::TYPES
     */
    public function getType();

    /**
     * Set the client type
     * @param int $type
     * @return $this
     * @since 1.0.0
     * @see self::TYPES
     */
    public function setType($type);

    /**
     * Get the client logo URI
     * @return string $logoUri
     * @since 1.0.0
     */
    public function getLogoUri();

    /**
     * Set the client logo URI
     * @param string $logoUri
     * @return $this
     * @since 1.0.0
     */
    public function setLogoUri($logoUri);

    /**
     * Get the client Terms of Service URI
     * @return string $tosUri
     * @since 1.0.0
     */
    public function getTermsOfServiceUri();

    /**
     * Set the client Terms of Service URI
     * @param string $tosUri
     * @return $this
     * @since 1.0.0
     */
    public function setTermsOfServiceUri($tosUri);

    /**
     * Get the client contacts information
     * @return string $contacts
     * @since 1.0.0
     */
    public function getContacts();

    /**
     * Set the client contacts information
     * @param string $contacts
     * @return $this
     * @since 1.0.0
     */
    public function setContacts($contacts);

    /**
     * @inheritdoc
     * @return string[]|null
     */
    public function getRedirectUri();

    /**
     * Sets the one or multiple redirect URIs
     * @param string|string[]|null $uri
     * @return $this
     * @since 1.0.0
     */
    public function setRedirectUri($uri);

    /**
     * Get the post logout redirect URI(s)
     * @return string[]|null
     * @since 1.0.0
     */
    public function getPostLogoutRedirectUris();

    /**
     * Sets the one or multiple post logout redirect URI(s)
     * @param string|string[]|null $postLogoutRedirectUris
     * @return $this
     * @since 1.0.0
     */
    public function setPostLogoutRedirectUris($postLogoutRedirectUris);

    /**
     * Get configuration for parsing environment variables in the redirect URI(s) and/or
     * secrets (`secret` and `oldSecret`).
     *
     * @return array{
     *             redirectUris?: array{
     *                 allowList: string[],
     *                 denyList?: string[]|null,
     *                 parseNested?: bool,
     *                 exceptionWhenNotSet?: bool,
     *                 exceptionWhenNotAllowed?: bool,
     *             },
     *             secrets?: array{
     *                 allowList: string[],
     *                 denyList?: string[]|null,
     *                 parseNested?: bool,
     *                 exceptionWhenNotSet?: bool,
     *                 exceptionWhenNotAllowed?: bool,
     *             },
     *         }|null
     * @since 1.0.0
     * @see \rhertogh\Yii2Oauth2Server\helpers\EnvironmentHelper::parseEnvVars()
     */
    public function getEnvVarConfig();

    /**
     * When configured, environment variables specified in the redirect URI(s) and/or
     * secrets (`secret` and `oldSecret`) will be replaced by their values.
     *
     * @param array{
     *            redirectUris?: array{
     *                allowList: string[],
     *                denyList?: string[]|null,
     *                parseNested?: bool,
     *                exceptionWhenNotSet?: bool,
     *                exceptionWhenNotAllowed?: bool,
     *            },
     *            secrets?: array{
     *                allowList: string[],
     *                denyList?: string[]|null,
     *                parseNested?: bool,
     *                exceptionWhenNotSet?: bool,
     *                exceptionWhenNotAllowed?: bool,
     *            },
     *        }|null $config
     * @return $this
     * @since 1.0.0
     * @see \rhertogh\Yii2Oauth2Server\helpers\EnvironmentHelper::parseEnvVars()
     */
    public function setEnvVarConfig($config);

    /**
     * Get configuration for parsing environment variables in the redirect URI(s).
     * Falls back to the Oauth2Module::$clientRedirectUrisEnvVarConfig if there is no specific configuration for this
     * client.
     * Please see `EnvironmentHelper::parseEnvVars()` for more details.
     *
     * @return array{
     *             allowList: string[],
     *             denyList?: string[]|null,
     *             parseNested?: bool,
     *             exceptionWhenNotSet?: bool,
     *             exceptionWhenNotAllowed?: bool,
     *         }|null
     * @since 1.0.0
     * @see \rhertogh\Yii2Oauth2Server\helpers\EnvironmentHelper::parseEnvVars()
     */
    public function getRedirectUrisEnvVarConfig();

    /**
     * Get configuration for parsing environment variables in the secrets (`secret` and `oldSecret`).
     * Please see `EnvironmentHelper::parseEnvVars()` for more details.
     *
     * @return array{
     *             allowList: string[],
     *             denyList?: string[]|null,
     *             parseNested?: bool,
     *             exceptionWhenNotSet?: bool,
     *             exceptionWhenNotAllowed?: bool,
     *         }|null
     * @since 1.0.0
     * @see \rhertogh\Yii2Oauth2Server\helpers\EnvironmentHelper::parseEnvVars()
     */
    public function getSecretsEnvVarConfig();

    /**
     * Validate the client against the full redirect URI, or allow for a variable "query" part.
     * @return bool
     * @see setAllowVariableRedirectUriQuery
     * @since 1.0.0
     */
    public function isVariableRedirectUriQueryAllowed();

    /**
     * By default, the client is validated against the full redirect URI including the "query" part.
     * If the "query" part of the return uri is variable it may be marked as such.
     *
     * Warning: Using a variable return uri query should be avoided, since it might introduce an attack vector.
     *          You probably should store and use the "state" on the client side.
     * @param bool $allowVariableRedirectUriQuery
     * @return $this
     * @since 1.0.0
     */
    public function setAllowVariableRedirectUriQuery($allowVariableRedirectUriQuery);

    /**
     * Is the client is allowed to use "generic" scopes. By default, scopes must be explicitly linked to the client.
     * @return bool
     * @since 1.0.0
     */
    public function getAllowGenericScopes();

    /**
     * Set if the client is allowed to use "generic" scopes. By default, scopes must be explicitly linked to the client.
     * @param bool $allowGenericScopes
     * @return $this
     * @since 1.0.0
     */
    public function setAllowGenericScopes($allowGenericScopes);

    /**
     * Will the server throw an exception when an unknown scope is requested by the client.
     * Note: If this setting is not explicitly defined for the client, the Oauth2Module's value will be used.
     * @return bool|null
     * @since 1.0.0
     */
    public function getExceptionOnInvalidScope();

    /**
     * Set if server throws an exception when an unknown scope is requested by the client.
     * @param bool|null $exceptionOnInvalidScope
     * @return $this
     * @since 1.0.0
     */
    public function setExceptionOnInvalidScope($exceptionOnInvalidScope);

    /**
     * Get the Grant Types enabled for the client.
     * @return integer
     * @since 1.0.0
     */
    public function getGrantTypes();

    /**
     * Set the Grant Types enabled for the client.
     *
     * @param int $grantTypes
     * @return $this
     * @since 1.0.0
     */
    public function setGrantTypes($grantTypes);

    /**
     * Validates if a Grant Type is enabled for the client.
     * @return bool
     * @since 1.0.0
     */
    public function validateGrantType($grantTypeIdentifier);

    /**
     * Set the client secret. It will be encrypted by the cryptographer.
     * Note: For security if $oldSecretValidUntil is not specified the old secret will be cleared
     * (regardless if it was expired or not).
     *
     * @param string|null $secret
     * @param Oauth2CryptographerInterface $cryptographer
     * @param \DateTimeImmutable|\DateInterval|null $oldSecretValidUntil
     * @param string|null $keyName The name of the key to use for the encryption
     * (must be present in the available keys).
     * @return $this
     * @since 1.0.0
     */
    public function setSecret($secret, $cryptographer, $oldSecretValidUntil = null, $keyName = null);

    /**
     * Set the client secret (and optionally the old_secret) to environment variable names.
     *
     * @param string $secretEnvVarName
     * @param string|null $oldSecretEnvVarName
     * @param \DateTimeImmutable|\DateInterval|null $oldSecretValidUntil
     * @return $this
     * @since 1.0.0
     */
    public function setSecretsAsEnvVars($secretEnvVarName, $oldSecretEnvVarName = null, $oldSecretValidUntil = null);

    /**
     * Validate new secret against the validation rules.
     * @param string $secret
     * @param string|null $error Will contain the error message in case the secret is invalid.
     * @return bool
     * @since 1.0.0
     * @see getMinimumSecretLength
     */
    public function validateNewSecret($secret, &$error);

    /**
     * Get the minimum length for new client secrets.
     * @return integer
     * @since 1.0.0
     */
    public function getMinimumSecretLength();

    /**
     * Set the minimum length for new client secrets.
     *
     * @param int $minimumSecretLength
     * @return $this
     * @since 1.0.0
     */
    public function setMinimumSecretLength($minimumSecretLength);

    /**
     * Get the decrypted secret.
     * @param Oauth2CryptographerInterface $cryptographer
     * @return string
     * @since 1.0.0
     */
    public function getDecryptedSecret($cryptographer);

    /**
     * Get the decrypted old secret.
     * @param Oauth2CryptographerInterface $cryptographer
     * @return string
     * @since 1.0.0
     */
    public function getDecryptedOldSecret($cryptographer);

    /**
     * Get the old secret expiry date/time.
     * @return \DateTimeImmutable
     * @since 1.0.0
     */
    public function getOldSecretValidUntil();

    /**
     * Validate a secret against the stored one.
     * If an "old" secret is set (and its "valid until" date is valid) the secret will also be validated against it
     * in case the regular stored secret fails.
     * @param string $secret
     * @param Oauth2CryptographerInterface $cryptographer
     * @return bool
     * @since 1.0.0
     */
    public function validateSecret($secret, $cryptographer);

    /**
     * Validate the requested scopes for the client.
     * @param string[] $scopeIdentifiers
     * @return bool
     * @see getAllowedScopes()
     * @since 1.0.0
     */
    public function validateAuthRequestScopes($scopeIdentifiers, &$unknownScopes = [], &$unauthorizedScopes = []);

    /**
     * Get the requested scopes that are allowed for this client.
     * When $requestedScopeIdentifiers is `true`, all possible scopes are returned.
     * @param string[]|true $requestedScopeIdentifiers
     * @return Oauth2ScopeInterface[]
     * @since 1.0.0
     */
    public function getAllowedScopes($requestedScopeIdentifiers = []);

    /**
     * Get the ClientScopes for this client.
     * @return Oauth2ClientScopeQueryInterface
     * @since 1.0.0
     */
    public function getClientScopes();

    /**
     * Get user account selection configuration for this client.
     * @return int|null
     * @since 1.0.0
     * @see Oauth2Module::USER_ACCOUNT_SELECTION_ALWAYS
     */
    public function getUserAccountSelection();

    /**
     * Set user account selection configuration for this client.
     * @param int|null $userAccountSelectionConfig
     * @since 1.0.0
     * @return $this
     * @see Oauth2Module::USER_ACCOUNT_SELECTION_ALWAYS
     */
    public function setUserAccountSelection($userAccountSelectionConfig);

    /**
     * Are end-user allowed to authorize this client.
     * @return bool
     * @since 1.0.0
     */
    public function endUsersMayAuthorizeClient();

    /**
     * Set if end-users are allowed to authorize this client.
     * @param bool $endUsersMayAuthorizeClient
     * @since 1.0.0
     * @return $this
     */
    public function setEndUsersMayAuthorizeClient($endUsersMayAuthorizeClient);

    /**
     * Are authorization code requests without PKCE allowed.
     * @return bool
     * @since 1.0.0
     */
    public function isAuthCodeWithoutPkceAllowed();

    /**
     * Set if authorization code requests without PKCE are allowed.
     * @param bool $allowAuthCodeWithoutPkce
     * @return $this
     * @since 1.0.0
     */
    public function setAllowAuthCodeWithoutPkce($allowAuthCodeWithoutPkce);

    /**
     * Should client authorization by the user be skipped if all scopes are allowed.
     * @return bool
     * @since 1.0.0
     */
    public function skipAuthorizationIfScopeIsAllowed();

    /**
     * Set if client authorization by the user should be skipped if all scopes are allowed.
     * @param bool $skipAuthIfScopeIsAllowed
     * @return $this
     * @since 1.0.0
     */
    public function setSkipAuthorizationIfScopeIsAllowed($skipAuthIfScopeIsAllowed);

    /**
     * Get the user id for clients that use the 'client credentials' Grant Type.
     * @return int|string|null
     * @since 1.0.0
     */
    public function getClientCredentialsGrantUserId();

    /**
     * Set the user id for clients that use the 'client credentials' Grant Type.
     * @param int|string|null $userId
     * @return $this
     * @since 1.0.0
     */
    public function setClientCredentialsGrantUserId($userId);

    /**
     * Warning! Enabling this setting might introduce privacy concerns since the client could poll for the online status
     * of a user.
     *
     * @return bool If this setting is disabled in case of OpenID Connect Context the Access Token won't include a
     * Refresh Token when the 'offline_access' scope is not included in the authorization request.
     * In some cases it might be needed to always include a Refresh Token, in that case enable this setting and
     * implement the `Oauth2OidcUserSessionStatusInterface` on the User Identity model.
     * @since 1.0.0
     */
    public function getOpenIdConnectAllowOfflineAccessWithoutConsent();

    /**
     * Warning! Enabling this setting might introduce privacy concerns since the client could poll for the online status
     * of a user.
     *
     * @param bool $allowOfflineAccessWithoutConsent If this setting is disabled in case of OpenID Connect Context the
     * Access Token won't include a Refresh Token when the 'offline_access' scope is not included in the authorization
     * request.
     * In some cases it might be needed to always include a Refresh Token, in that case enable this setting and
     * implement the `Oauth2OidcUserSessionStatusInterface` on the User Identity model.
     * @return $this
     * @since 1.0.0
     */
    public function setOpenIdConnectAllowOfflineAccessWithoutConsent($allowOfflineAccessWithoutConsent);

    /**
     * @return int Get RP Initiated Logout configuration.
     * @since 1.0.0
     * @see OIDC_RP_INITIATED_LOGOUT_OPTIONS
     * @see https://openid.net/specs/openid-connect-rpinitiated-1_0.html
     */
    public function getOpenIdConnectRpInitiatedLogout();

    /**
     * @param int $rpInitiatedLogout Configure RP Initiated Logout.
     * @return $this
     * @since 1.0.0
     * @see OIDC_RP_INITIATED_LOGOUT_OPTIONS
     * @see https://openid.net/specs/openid-connect-rpinitiated-1_0.html
     */
    public function setOpenIdConnectRpInitiatedLogout($rpInitiatedLogout);

    /**
     * The encryption algorithm to use for the OpenID Connect Userinfo Endpoint. When `null`, no encryption is applied.
     * @see https://openid.net/specs/openid-connect-registration-1_0.html#ClientMetadata
     * @see https://datatracker.ietf.org/doc/html/rfc7518
     * @return string|null
     * @since 1.0.0
     */
    public function getOpenIdConnectUserinfoEncryptedResponseAlg();

    /**
     * Set the encryption algorithm to use for the OpenID Connect Userinfo Endpoint.
     * When `null`, no encryption is applied.
     * @see https://openid.net/specs/openid-connect-registration-1_0.html#ClientMetadata
     * @see https://datatracker.ietf.org/doc/html/rfc7518
     * @param string|null $algorithm
     * @return $this
     * @since 1.0.0
     */
    public function setOpenIdConnectUserinfoEncryptedResponseAlg($algorithm);

    /**
     * @param string|string[]|array[]|Oauth2ClientScopeInterface[]|Oauth2ScopeInterface[]|null $scopes
     * @param ScopeRepositoryInterface $scopeRepository
     * @return array{
     *     unaffected: Oauth2ClientScopeInterface[],
     *     new: Oauth2ClientScopeInterface[],
     *     updated: Oauth2ClientScopeInterface[],
     *     deleted: Oauth2ClientScopeInterface[],
     * }
     * @since 1.0.0
     */
    public function syncClientScopes($scopes, $scopeRepository);
}
