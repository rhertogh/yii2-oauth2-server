<?php

namespace rhertogh\Yii2Oauth2Server\interfaces\components\openidconnect\scope;

use yii\base\Configurable;

interface Oauth2OidcScopeInterface extends Configurable
{
    /**
     * OpenID Connect Authentication Request scope claim
     * @see https://openid.net/specs/openid-connect-core-1_0.html#AuthRequest
     * @since 1.0.0
     */
    public const OPENID_CONNECT_SCOPE_OPENID = 'openid';
    /**
     * This scope value requests access to the End-User's default profile Claims
     * @see https://openid.net/specs/openid-connect-core-1_0.html#ScopeClaims
     * @since 1.0.0
     */
    public const OPENID_CONNECT_SCOPE_PROFILE = 'profile';
    /**
     * This scope value requests access to the email and email_verified Claims
     * @see https://openid.net/specs/openid-connect-core-1_0.html#ScopeClaims
     * @since 1.0.0
     */
    public const OPENID_CONNECT_SCOPE_EMAIL = 'email';
    /**
     * This scope value requests access to the phone_number and phone_number_verified Claims.
     * @see https://openid.net/specs/openid-connect-core-1_0.html#ScopeClaims
     * @since 1.0.0
     */
    public const OPENID_CONNECT_SCOPE_PHONE = 'phone';
    /**
     * This scope value requests access to the address Claim.
     * @see https://openid.net/specs/openid-connect-core-1_0.html#ScopeClaims
     * @since 1.0.0
     */
    public const OPENID_CONNECT_SCOPE_ADDRESS = 'address';
    /**
     * This scope value requests that an OAuth 2.0 Refresh Token be issued that can be used to obtain an Access Token
     * that grants access to the End-User's UserInfo Endpoint even when the End-User is not present (not logged in).
     * @see https://openid.net/specs/openid-connect-core-1_0.html#OfflineAccess
     * @since 1.0.0
     */
    public const OPENID_CONNECT_SCOPE_OFFLINE_ACCESS = 'offline_access';

    /**
     * OpenID Connect default scope claims.
     * @see https://openid.net/specs/openid-connect-core-1_0.html#ScopeClaims
     * @since 1.0.0
     */
    public const OPENID_CONNECT_DEFAULT_SCOPE_CLAIMS = [
        self::OPENID_CONNECT_SCOPE_OPENID => [
            Oauth2OidcClaimInterface::OPENID_CONNECT_CLAIM_SUB,
            Oauth2OidcClaimInterface::OPENID_CONNECT_CLAIM_AUTH_TIME,
            Oauth2OidcClaimInterface::OPENID_CONNECT_CLAIM_NONCE,
        ],
        self::OPENID_CONNECT_SCOPE_PROFILE => [
            Oauth2OidcClaimInterface::OPENID_CONNECT_CLAIM_NAME,
            Oauth2OidcClaimInterface::OPENID_CONNECT_CLAIM_FAMILY_NAME,
            Oauth2OidcClaimInterface::OPENID_CONNECT_CLAIM_GIVEN_NAME,
            Oauth2OidcClaimInterface::OPENID_CONNECT_CLAIM_MIDDLE_NAME,
            Oauth2OidcClaimInterface::OPENID_CONNECT_CLAIM_NICKNAME,
            Oauth2OidcClaimInterface::OPENID_CONNECT_CLAIM_PREFERRED_USERNAME,
            Oauth2OidcClaimInterface::OPENID_CONNECT_CLAIM_PROFILE,
            Oauth2OidcClaimInterface::OPENID_CONNECT_CLAIM_PICTURE,
            Oauth2OidcClaimInterface::OPENID_CONNECT_CLAIM_WEBSITE,
            Oauth2OidcClaimInterface::OPENID_CONNECT_CLAIM_GENDER,
            Oauth2OidcClaimInterface::OPENID_CONNECT_CLAIM_BIRTHDATE,
            Oauth2OidcClaimInterface::OPENID_CONNECT_CLAIM_ZONEINFO,
            Oauth2OidcClaimInterface::OPENID_CONNECT_CLAIM_LOCALE,
            Oauth2OidcClaimInterface::OPENID_CONNECT_CLAIM_UPDATED_AT,
        ],
        self::OPENID_CONNECT_SCOPE_EMAIL => [
            Oauth2OidcClaimInterface::OPENID_CONNECT_CLAIM_EMAIL,
            Oauth2OidcClaimInterface::OPENID_CONNECT_CLAIM_EMAIL_VERIFIED,
        ],
        self::OPENID_CONNECT_SCOPE_ADDRESS => [
            Oauth2OidcClaimInterface::OPENID_CONNECT_CLAIM_ADDRESS,
        ],
        self::OPENID_CONNECT_SCOPE_PHONE => [
            Oauth2OidcClaimInterface::OPENID_CONNECT_CLAIM_PHONE_NUMBER,
            Oauth2OidcClaimInterface::OPENID_CONNECT_CLAIM_PHONE_NUMBER_VERIFIED,
        ],
    ];

    /**
     * Get the identifier for this scope. This can be a default OpenID Connect scope or a custom one.
     * @return string
     * @see OPENID_CONNECT_DEFAULT_SCOPE_CLAIMS
     * @since 1.0.0
     */
    public function getIdentifier();

    /**
     * Set the identifier for this scope. This can be a default OpenID Connect scope or a custom one.
     * @param string $identifier
     * @return $this
     * @since 1.0.0
     */
    public function setIdentifier($identifier);

    /**
     * Get the claims for this scope.
     * @return Oauth2OidcClaimInterface[]
     * @since 1.0.0
     */
    public function getClaims();

    /**
     * Set the claims for this scope.
     * @param Oauth2OidcClaimInterface[] $claims
     * @return $this
     * @since 1.0.0
     */
    public function setClaims($claims);

    /**
     * Add claims to this scope.
     * @param Oauth2OidcClaimInterface[]|array[]|string[] $claims The value type of each item determines the behavior:
     *  - Oauth2OidcClaimInterface: will be used as is.
     *  - string: If the item's key is numeric the value is used as the claim's 'identifier',
     *            e.g ['my_claim_identifier'].
     *            Otherwise, the key is used as 'identifier' and the value as 'determiner,
     *            e.g. ['my_claim_identifier' => 'my_determiner'].
     *  - array: If the item's key is a string, the key is used as the claim's 'identifier',
     *           e.g ['my_claim_identifier' => [... claim config ... ]].
     *           Otherwise, the 'identifier' must be present in the claim config,
     *           e.g. [['identifier' => 'my_claim_identifier', 'determiner' => 'my_determiner']].
     * @return $this
     * @since 1.0.0
     */
    public function addClaims($claims);

    /**
     * Clear all defined claims for this scope.
     * @return $this
     * @since 1.0.0
     */
    public function clearClaims();

    /**
     * Get a specific claim for this scope.
     * @param string $claimIdentifier
     * @return Oauth2OidcClaimInterface|null
     * @since 1.0.0
     */
    public function getClaim($claimIdentifier);

    /**
     * Add a claim to this scope.
     * @param Oauth2OidcClaimInterface|array|string $claim The value type determines the behavior:
     *  - Oauth2OidcClaimInterface: will be used as is.
     *  - string: Will be used as the 'identifier' for the claim.
     *  - array:  Will be used as Claim config
     *            e.g. ['identifier' => 'my_claim_identifier', 'determiner' => 'my_determiner'].
     * @return $this
     * @since 1.0.0
     */
    public function addClaim(Oauth2OidcClaimInterface $claim);

    /**
     * Remove a claim from the scope.
     * @param string $claimIdentifier
     * @return $this
     * @since 1.0.0
     */
    public function removeClaim($claimIdentifier);

    /**
     * Check if the scope has a specific claim.
     * @param string $claimIdentifier
     * @return bool
     * @since 1.0.0
     */
    public function hasClaim($claimIdentifier);
}
