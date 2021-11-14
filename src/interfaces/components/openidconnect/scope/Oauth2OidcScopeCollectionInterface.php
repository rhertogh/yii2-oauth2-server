<?php

namespace rhertogh\Yii2Oauth2Server\interfaces\components\openidconnect\scope;

use yii\base\Configurable;
use yii\base\InvalidArgumentException;

interface Oauth2OidcScopeCollectionInterface extends Configurable
{
    /**
     * Default scopes specified by OpenID Connect
     * @see https://openid.net/specs/openid-connect-core-1_0.html#ScopeClaims
     * @since 1.0.0
     */
    public const OPENID_CONNECT_DEFAULT_SCOPES = [
        Oauth2OidcScopeInterface::OPENID_CONNECT_SCOPE_OPENID,
        Oauth2OidcScopeInterface::OPENID_CONNECT_SCOPE_PROFILE,
        Oauth2OidcScopeInterface::OPENID_CONNECT_SCOPE_EMAIL,
        Oauth2OidcScopeInterface::OPENID_CONNECT_SCOPE_ADDRESS,
        Oauth2OidcScopeInterface::OPENID_CONNECT_SCOPE_PHONE,
    ];

    /**
     * Get all scopes set in this collection.
     * @return Oauth2OidcScopeInterface[]
     * @since 1.0.0
     */
    public function getOidcScopes();

    /**
     * Set scopes for this collection.
     * @param Oauth2OidcScopeInterface[]|array[]|string[] $oidcScopes
     * @return $this
     * @since 1.0.0
     */
    public function setOidcScopes($oidcScopes);

    /**
     * Add scopes to this collection.
     * @param Oauth2OidcScopeInterface[]|array[]|string[] $oidcScopes The value type of each item determines
     * the behavior:
     *  - Oauth2OidcScopeInterface: will be used as is.
     *  - string: will be added as default OpenID Connect scope by its name.
     *  - array: If the item's key is numeric the value is used as scope configuration (see addOidcScope()),
     *           e.g. [['identifier' => 'my_scope_identifier', 'claims' => ['my_claim_identifier']]].
     *           Otherwise, the key is used as scope 'identifier' and the value as claims,
     *           e.g. ['my_claim_identifier' => ['my_claim_identifier']].
     *
     * @return $this
     * @see addOidcScope()
     * @since 1.0.0
     */
    public function addOidcScopes($oidcScopes);

    /**
     * Removes all defined scopes from the collection.
     * @return $this
     * @since 1.0.0
     */
    public function clearOidcScopes();

    /**
     * Get a specific scope.
     * @param string $scopeIdentifier
     * @return Oauth2OidcScopeInterface|null
     * @since 1.0.0
     */
    public function getOidcScope($scopeIdentifier);

    /**
     * Add a scope to this collection.
     * @param Oauth2OidcScopeInterface|array|string $oidcScope The value type determines the behavior:
     *  - Oauth2OidcScopeInterface: will be used as is.
     *  - string: Will add a default OpenID Connect scope
     *  - array:  Will be used as Scope config
     *            e.g. ['identifier' => 'my_scope_identifier', 'claims' => ['my_claim_identifier']].
     * @return $this
     * @see getDefaultOidcScope()
     * @since 1.0.0
     */
    public function addOidcScope($oidcScope);

    /**
     * Remove a specific scope from the collection
     * @param string $scopeIdentifier
     * @return $this
     * @since 1.0.0
     */
    public function removeOidcScope($scopeIdentifier);

    /**
     * Check if the collection has a specific scope
     * @param string $scopeIdentifier
     * @return bool
     * @since 1.0.0
     */
    public function hasOidcScope($scopeIdentifier);

    /**
     * Get a predefined OpenID Connect scope
     * @param string $scopeIdentifier
     * @return Oauth2OidcScopeInterface
     * @throws InvalidArgumentException
     * @see OPENID_CONNECT_DEFAULT_SCOPES
     * @since 1.0.0
     */
    public function getDefaultOidcScope($scopeIdentifier);

    /**
     * Returns all scopes and claims that are defined in the collection.
     * @return array{scopeIdentifiers: string[], claimIdentifiers: string[]} Format:
     * ```php
     * [
     *  'scopeIdentifiers' => ['openid', ...],
     *  'claimIdentifiers' => ['sub', 'nonce', ...],
     *  ]
     * ```
     * @since 1.0.0
     */
    public function getSupportedScopeAndClaimIdentifiers();

    /**
     * Get claims that are defined in the collection for the specified scopes.
     * @param string[] $scopeIdentifiers
     * @return Oauth2OidcClaimInterface[]
     * @since 1.0.0
     */
    public function getFilteredClaims($scopeIdentifiers);
}
