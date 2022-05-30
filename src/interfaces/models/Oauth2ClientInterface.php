<?php

namespace rhertogh\Yii2Oauth2Server\interfaces\models;

use League\OAuth2\Server\Entities\ClientEntityInterface;
use rhertogh\Yii2Oauth2Server\interfaces\components\encryption\Oauth2EncryptorInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\base\Oauth2ActiveRecordIdInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\base\Oauth2EnabledInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\base\Oauth2IdentifierInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\queries\Oauth2ClientQueryInterface;
use rhertogh\Yii2Oauth2Server\Oauth2Module;
use yii\db\ActiveQuery;

interface Oauth2ClientInterface extends
    Oauth2ActiveRecordIdInterface,
    Oauth2IdentifierInterface,
    Oauth2EnabledInterface,
    ClientEntityInterface
{
    /**
     * Client Type "Confidential": Identifies the client via a shared secret.
     * Note: This should only be trusted in case the client can store the secret securely, e.g. another server.
     * @since 1.0.0
     */
    public const TYPE_CONFIDENTIAL = 1;
    /**
     * Client Type "Public": In case the client can not store a secret securely it should be declared public,
     * e.g. web- or mobile applications.
     * @since 1.0.0
     */
    public const TYPE_PUBLIC = 2;
    /**
     * Client Types
     * @since 1.0.0
     */
    public const TYPES = [
        self::TYPE_CONFIDENTIAL,
        self::TYPE_PUBLIC,
    ];

    /**
     * Scope access "strict" will throw an exception if the client requests a scope not defined for the client.
     * @since 1.0.0
     */
    public const SCOPE_ACCESS_STRICT = 0;
    /**
     * Scope access "strict quite" will only grant scopes defined for the client, other scopes will be silently ignored.
     * @since 1.0.0
     */
    public const SCOPE_ACCESS_STRICT_QUIET = 1;
    /**
     * Scope access "permissive" will grant any scopes requested that are defined in general
     * (not necessarily defined for the client). An exception will be thrown if the client requests a scope not defined.
     * @since 1.0.0
     */
    public const SCOPE_ACCESS_PERMISSIVE = 2;
    /**
     * Scope Access options
     * @since 1.0.0
     */
    public const SCOPE_ACCESSES = [
        self::SCOPE_ACCESS_STRICT,
        self::SCOPE_ACCESS_STRICT_QUIET,
        self::SCOPE_ACCESS_PERMISSIVE,
    ];

    /**
     * @inheritDoc
     * @return Oauth2ClientQueryInterface|ActiveQuery
     */
    public static function find();

    /**
     * Sets the one or multiple redirect URIs
     * @param string|string[] $uri
     * @since 1.0.0
     */
    public function setRedirectUri($uri);

    /**
     * Get the client's scope access.
     * @return int
     * @see SCOPE_ACCESSES
     * @since 1.0.0
     */
    public function getScopeAccess();

    /**
     * Get the Grant Types enabled for the client.
     * @return integer
     * @since 1.0.0
     */
    public function getGrantTypes();

    /**
     * Validates if a Grant Type is enabled for the client.
     * @return bool
     * @since 1.0.0
     */
    public function validateGrantType($grantTypeIdentifier);

    /**
     * Set the client secret. It will be encrypted by the encryptor.
     * Note: For security if $oldSecretValidUntil is not specified the old secret will be cleared
     * (regardless if it was expired or not).
     *
     * @param string|null $secret
     * @param Oauth2EncryptorInterface $encryptor
     * @param \DateTimeImmutable|\DateInterval|null $oldSecretValidUntil
     * @param string|null $keyName The name of the key to use for the encryption
     * (must be present in the available keys).
     * @since 1.0.0
     */
    public function setSecret($secret, $encryptor, $oldSecretValidUntil = null, $keyName = null);

    /**
     * Validate new secret against the validation rules.
     * @param string $secret
     * @param string|null $error Will contain the error message in case the secret is invalid.
     * @return bool
     * @since 1.0.0
     */
    public function validateNewSecret($secret, &$error);

    /**
     * Get the decrypted secret.
     * @param Oauth2EncryptorInterface $encryptor
     * @return string
     * @since 1.0.0
     */
    public function getDecryptedSecret($encryptor);

    /**
     * Get the decrypted old secret.
     * @param Oauth2EncryptorInterface $encryptor
     * @return string
     * @since 1.0.0
     */
    public function getDecryptedOldSecret($encryptor);

    /**
     * Get the old secret expiry date/time.
     * @return \DateTimeImmutable
     * @since 1.0.0
     */
    public function getOldSecretValidUntil();

    /**
     * Validate a secret against the stored one.
     * If an "old" secret is set (and it's "valid until" date is valid) the secret will also be validated against it
     * in case the regular stored secret fails.
     * @param string $secret
     * @param Oauth2EncryptorInterface $encryptor
     * @return bool
     * @since 1.0.0
     */
    public function validateSecret($secret, $encryptor);

    /**
     * Validate the requested scopes for the client.
     * @param string[] $scopeIdentifiers
     * @return bool
     * @see getScopeAccess()
     * @since 1.0.0
     */
    public function validateAuthRequestScopes($scopeIdentifiers, &$unauthorizedScopes = []);

    /**
     * Get the requested scopes that are allowed for this client.
     * @param string[] $requestedScopeIdentifiers
     * @return Oauth2ScopeInterface[]
     * @since 1.0.0
     */
    public function getAllowedScopes($requestedScopeIdentifiers = []);

    /**
     * Get user account selection configuration for this client.
     * @return int|null
     * @since 1.0.0
     * @see Oauth2Module::USER_ACCOUNT_SELECTION_ALWAYS
     */
    public function getUserAccountSelection();

    /**
     * Are authorization code requests without PKCE allowed.
     * @return bool
     * @since 1.0.0
     */
    public function isAuthCodeWithoutPkceAllowed();

    /**
     * Should client authorization by the user be skipped if all scopes are allowed.
     * @return bool
     * @since 1.0.0
     */
    public function skipAuthorizationIfScopeIsAllowed();

    /**
     * Get the user id for clients that use the 'client credentials' Grant Type.
     * @return int|string|null
     * @since 1.0.0
     */
    public function getClientCredentialsGrantUserId();

    /**
     * Warning! Enabling this setting might introduce privacy concerns since the client could poll for the online status
     * of a user.
     *
     * @var bool If this setting is disabled in case of OpenID Connect Context the Access Token won't include a
     * Refresh Token when the 'offline_access' scope is not included in the authorization request.
     * In some cases it might be needed to always include a Refresh Token, in that case enable this setting and
     * implement the `Oauth2OidcUserSessionStatusInterface` on the User Identity model.
     * @return bool
     * @since 1.0.0
     */
    public function getOpenIdConnectAllowOfflineAccessWithoutConsent();

    /**
     * The encryption algorithm to use for the OpenID Connect Userinfo Endpoint. WHen `null`, no encryption is applied.
     * @see https://openid.net/specs/openid-connect-registration-1_0.html#ClientMetadata
     * @see https://datatracker.ietf.org/doc/html/rfc7518
     * @return string|null
     * @since 1.0.0
     */
    public function getOpenIdConnectUserinfoEncryptedResponseAlg();
}
