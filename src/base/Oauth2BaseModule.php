<?php

namespace rhertogh\Yii2Oauth2Server\base;

use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Signer\Rsa\Sha256;
use League\OAuth2\Server\CryptKey;
use rhertogh\Yii2Oauth2Server\components\authorization\Oauth2ClientAuthorizationRequest;
use rhertogh\Yii2Oauth2Server\components\authorization\Oauth2ScopeAuthorizationRequest;
use rhertogh\Yii2Oauth2Server\components\encryption\Oauth2Cryptographer;
use rhertogh\Yii2Oauth2Server\components\factories\encryption\Oauth2EncryptionKeyFactory;
use rhertogh\Yii2Oauth2Server\components\factories\grants\Oauth2AuthCodeGrantFactory;
use rhertogh\Yii2Oauth2Server\components\factories\grants\Oauth2ClientCredentialsGrantFactory;
use rhertogh\Yii2Oauth2Server\components\factories\grants\Oauth2ImplicitGrantFactory;
use rhertogh\Yii2Oauth2Server\components\factories\grants\Oauth2PasswordGrantFactory;
use rhertogh\Yii2Oauth2Server\components\factories\grants\Oauth2PersonalAccessTokenGrantFactory;
use rhertogh\Yii2Oauth2Server\components\factories\grants\Oauth2RefreshTokenGrantFactory;
use rhertogh\Yii2Oauth2Server\components\openidconnect\claims\Oauth2OidcClaim;
use rhertogh\Yii2Oauth2Server\components\openidconnect\scopes\Oauth2OidcScope;
use rhertogh\Yii2Oauth2Server\components\openidconnect\scopes\Oauth2OidcScopeCollection;
use rhertogh\Yii2Oauth2Server\components\openidconnect\server\responses\Oauth2OidcBearerTokenResponse;
use rhertogh\Yii2Oauth2Server\components\repositories\Oauth2AccessTokenRepository;
use rhertogh\Yii2Oauth2Server\components\repositories\Oauth2AuthCodeRepository;
use rhertogh\Yii2Oauth2Server\components\repositories\Oauth2ClientRepository;
use rhertogh\Yii2Oauth2Server\components\repositories\Oauth2RefreshTokenRepository;
use rhertogh\Yii2Oauth2Server\components\repositories\Oauth2ScopeRepository;
use rhertogh\Yii2Oauth2Server\components\repositories\Oauth2UserRepository;
use rhertogh\Yii2Oauth2Server\components\server\grants\Oauth2AuthCodeGrant;
use rhertogh\Yii2Oauth2Server\components\server\grants\Oauth2ClientCredentialsGrant;
use rhertogh\Yii2Oauth2Server\components\server\grants\Oauth2ImplicitGrant;
use rhertogh\Yii2Oauth2Server\components\server\grants\Oauth2PasswordGrant;
use rhertogh\Yii2Oauth2Server\components\server\grants\Oauth2PersonalAccessTokenGrant;
use rhertogh\Yii2Oauth2Server\components\server\grants\Oauth2RefreshTokenGrant;
use rhertogh\Yii2Oauth2Server\components\server\Oauth2AuthorizationServer;
use rhertogh\Yii2Oauth2Server\components\server\Oauth2ResourceServer;
use rhertogh\Yii2Oauth2Server\components\server\responses\Oauth2BearerTokenResponse;
use rhertogh\Yii2Oauth2Server\controllers\web\Oauth2CertificatesController;
use rhertogh\Yii2Oauth2Server\controllers\web\Oauth2ConsentController;
use rhertogh\Yii2Oauth2Server\controllers\web\Oauth2OidcController;
use rhertogh\Yii2Oauth2Server\controllers\web\Oauth2ServerController;
use rhertogh\Yii2Oauth2Server\controllers\web\Oauth2WellKnownController;
use rhertogh\Yii2Oauth2Server\interfaces\components\authorization\Oauth2ClientAuthorizationRequestInterface;
use rhertogh\Yii2Oauth2Server\interfaces\components\authorization\Oauth2ScopeAuthorizationRequestInterface;
use rhertogh\Yii2Oauth2Server\interfaces\components\encryption\Oauth2CryptographerInterface;
use rhertogh\Yii2Oauth2Server\interfaces\components\factories\encryption\Oauth2EncryptionKeyFactoryInterface;
use rhertogh\Yii2Oauth2Server\interfaces\components\factories\grants\Oauth2AuthCodeGrantFactoryInterface;
use rhertogh\Yii2Oauth2Server\interfaces\components\factories\grants\Oauth2ClientCredentialsGrantFactoryInterface;
use rhertogh\Yii2Oauth2Server\interfaces\components\factories\grants\Oauth2ImplicitGrantFactoryInterface;
use rhertogh\Yii2Oauth2Server\interfaces\components\factories\grants\Oauth2PasswordGrantFactoryInterface;
use rhertogh\Yii2Oauth2Server\interfaces\components\factories\grants\Oauth2PersonalAccessTokenGrantFactoryInterface;
use rhertogh\Yii2Oauth2Server\interfaces\components\factories\grants\Oauth2RefreshTokenGrantFactoryInterface;
use rhertogh\Yii2Oauth2Server\interfaces\components\openidconnect\scope\Oauth2OidcClaimInterface;
use rhertogh\Yii2Oauth2Server\interfaces\components\openidconnect\scope\Oauth2OidcScopeCollectionInterface;
use rhertogh\Yii2Oauth2Server\interfaces\components\openidconnect\scope\Oauth2OidcScopeInterface;
use rhertogh\Yii2Oauth2Server\interfaces\components\openidconnect\server\responses\Oauth2OidcBearerTokenResponseInterface;
use rhertogh\Yii2Oauth2Server\interfaces\components\repositories\base\Oauth2RepositoryInterface;
use rhertogh\Yii2Oauth2Server\interfaces\components\repositories\Oauth2AccessTokenRepositoryInterface;
use rhertogh\Yii2Oauth2Server\interfaces\components\repositories\Oauth2AuthCodeRepositoryInterface;
use rhertogh\Yii2Oauth2Server\interfaces\components\repositories\Oauth2ClientRepositoryInterface;
use rhertogh\Yii2Oauth2Server\interfaces\components\repositories\Oauth2RefreshTokenRepositoryInterface;
use rhertogh\Yii2Oauth2Server\interfaces\components\repositories\Oauth2ScopeRepositoryInterface;
use rhertogh\Yii2Oauth2Server\interfaces\components\repositories\Oauth2UserRepositoryInterface;
use rhertogh\Yii2Oauth2Server\interfaces\components\server\grants\Oauth2AuthCodeGrantInterface;
use rhertogh\Yii2Oauth2Server\interfaces\components\server\grants\Oauth2ClientCredentialsGrantInterface;
use rhertogh\Yii2Oauth2Server\interfaces\components\server\grants\Oauth2ImplicitGrantInterface;
use rhertogh\Yii2Oauth2Server\interfaces\components\server\grants\Oauth2PasswordGrantInterface;
use rhertogh\Yii2Oauth2Server\interfaces\components\server\grants\Oauth2PersonalAccessTokenGrantInterface;
use rhertogh\Yii2Oauth2Server\interfaces\components\server\grants\Oauth2RefreshTokenGrantInterface;
use rhertogh\Yii2Oauth2Server\interfaces\components\server\Oauth2AuthorizationServerInterface;
use rhertogh\Yii2Oauth2Server\interfaces\components\server\Oauth2ResourceServerInterface;
use rhertogh\Yii2Oauth2Server\interfaces\components\server\responses\Oauth2BearerTokenResponseInterface;
use rhertogh\Yii2Oauth2Server\interfaces\controllers\web\Oauth2CertificatesControllerInterface;
use rhertogh\Yii2Oauth2Server\interfaces\controllers\web\Oauth2ConsentControllerInterface;
use rhertogh\Yii2Oauth2Server\interfaces\controllers\web\Oauth2OidcControllerInterface;
use rhertogh\Yii2Oauth2Server\interfaces\controllers\web\Oauth2ServerControllerInterface;
use rhertogh\Yii2Oauth2Server\interfaces\controllers\web\Oauth2WellKnownControllerInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\external\user\Oauth2OidcUserInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\Oauth2AccessTokenInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\Oauth2AccessTokenScopeInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\Oauth2AuthCodeInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\Oauth2AuthCodeScopeInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\Oauth2ClientInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\Oauth2ClientScopeInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\Oauth2RefreshTokenInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\Oauth2ScopeInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\Oauth2UserClientInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\Oauth2UserClientScopeInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\queries\Oauth2AccessTokenQueryInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\queries\Oauth2AccessTokenScopeQueryInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\queries\Oauth2AuthCodeQueryInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\queries\Oauth2AuthCodeScopeQueryInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\queries\Oauth2ClientQueryInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\queries\Oauth2ClientScopeQueryInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\queries\Oauth2RefreshTokenQueryInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\queries\Oauth2ScopeQueryInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\queries\Oauth2UserClientQueryInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\queries\Oauth2UserClientScopeQueryInterface;
use rhertogh\Yii2Oauth2Server\models\Oauth2AccessToken;
use rhertogh\Yii2Oauth2Server\models\Oauth2AccessTokenScope;
use rhertogh\Yii2Oauth2Server\models\Oauth2AuthCode;
use rhertogh\Yii2Oauth2Server\models\Oauth2AuthCodeScope;
use rhertogh\Yii2Oauth2Server\models\Oauth2Client;
use rhertogh\Yii2Oauth2Server\models\Oauth2ClientScope;
use rhertogh\Yii2Oauth2Server\models\Oauth2RefreshToken;
use rhertogh\Yii2Oauth2Server\models\Oauth2Scope;
use rhertogh\Yii2Oauth2Server\models\Oauth2UserClient;
use rhertogh\Yii2Oauth2Server\models\Oauth2UserClientScope;
use rhertogh\Yii2Oauth2Server\models\queries\Oauth2AccessTokenQuery;
use rhertogh\Yii2Oauth2Server\models\queries\Oauth2AccessTokenScopeQuery;
use rhertogh\Yii2Oauth2Server\models\queries\Oauth2AuthCodeQuery;
use rhertogh\Yii2Oauth2Server\models\queries\Oauth2AuthCodeScopeQuery;
use rhertogh\Yii2Oauth2Server\models\queries\Oauth2ClientQuery;
use rhertogh\Yii2Oauth2Server\models\queries\Oauth2ClientScopeQuery;
use rhertogh\Yii2Oauth2Server\models\queries\Oauth2RefreshTokenQuery;
use rhertogh\Yii2Oauth2Server\models\queries\Oauth2ScopeQuery;
use rhertogh\Yii2Oauth2Server\models\queries\Oauth2UserClientQuery;
use rhertogh\Yii2Oauth2Server\models\queries\Oauth2UserClientScopeQuery;
use Yii;
use yii\base\InvalidCallException;
use yii\base\InvalidConfigException;
use yii\base\Module;

/**
 * @property Oauth2OidcScopeCollectionInterface|array|callable|string $openIdConnectScopes;
 */
abstract class Oauth2BaseModule extends Module
{
    # region Supported grant types.
    # Note: These should match League\OAuth2\Server\Grant\GrantTypeInterface::getIdentifier() for their respective type.
    /**
     * "authorization_code" Grant Type.
     * @since 1.0.0
     */
    public const GRANT_TYPE_IDENTIFIER_AUTH_CODE = 'authorization_code';
    /**
     * "client_credentials" Grant Type.
     * @since 1.0.0
     */
    public const GRANT_TYPE_IDENTIFIER_CLIENT_CREDENTIALS = 'client_credentials';
    /**
     * "refresh_token" Grant Type.
     * @since 1.0.0
     */
    public const GRANT_TYPE_IDENTIFIER_REFRESH_TOKEN = 'refresh_token';
    /**
     * "implicit" Grant Type. Note: This is a legacy Grant Type.
     * @since 1.0.0
     */
    public const GRANT_TYPE_IDENTIFIER_IMPLICIT = 'implicit';
    /**
     * "password" Grant Type. Note: This is a legacy Grant Type.
     * @since 1.0.0
     */
    public const GRANT_TYPE_IDENTIFIER_PASSWORD = 'password';

    /**
     * "personal_access_token" Grant Type. Note: This is a custom grant type and not part of the Oauth2 specification.
     * @since 1.0.0
     */
    public const GRANT_TYPE_IDENTIFIER_PERSONAL_ACCESS_TOKEN = 'personal_access_token';

    /**
     * Supported grant type identifiers
     * @since 1.0.0
     */
    public const GRANT_TYPE_IDENTIFIERS = [
        self::GRANT_TYPE_IDENTIFIER_AUTH_CODE,
        self::GRANT_TYPE_IDENTIFIER_CLIENT_CREDENTIALS,
        self::GRANT_TYPE_IDENTIFIER_REFRESH_TOKEN,
        self::GRANT_TYPE_IDENTIFIER_IMPLICIT,
        self::GRANT_TYPE_IDENTIFIER_PASSWORD,
        self::GRANT_TYPE_IDENTIFIER_PERSONAL_ACCESS_TOKEN,
    ];
    # endregion Supported grant types

    # region Numeric IDs for Supported grant types
    /**
     * Numeric id for "authorization_code" Grant Type.
     * @since 1.0.0
     */
    public const GRANT_TYPE_AUTH_CODE = 1;
    /**
     * Numeric id for "client_credentials" Grant Type.
     * @since 1.0.0
     */
    public const GRANT_TYPE_CLIENT_CREDENTIALS = 2;
    /**
     * Numeric id for "refresh_token" Grant Type.
     * @since 1.0.0
     */
    public const GRANT_TYPE_REFRESH_TOKEN = 4;
    /**
     * Numeric id for "implicit" Grant Type. Note: This is a legacy Grant Type.
     * @since 1.0.0
     */
    public const GRANT_TYPE_PASSWORD = 1024; // Legacy Grant.
    /**
     * Numeric id for "password" Grant Type. Note: This is a legacy Grant Type.
     * @since 1.0.0
     */
    public const GRANT_TYPE_IMPLICIT = 2048; // Legacy Grant.
    /**
     * Numeric id for "personal_access_token" Grant Type.
     * Note: This is a custom grant type and not part of the Oauth2 specification.
     * @since 1.0.0
     */
    public const GRANT_TYPE_PERSONAL_ACCESS_TOKEN = 4096;
    # endregion Numeric IDs for Supported grant types

    /**
     * Mapping between Grant Type identifier and its numeric id.
     * @since 1.0.0
     */
    public const GRANT_TYPE_MAPPING = [
        self::GRANT_TYPE_IDENTIFIER_AUTH_CODE => self::GRANT_TYPE_AUTH_CODE,
        self::GRANT_TYPE_IDENTIFIER_CLIENT_CREDENTIALS => self::GRANT_TYPE_CLIENT_CREDENTIALS,
        self::GRANT_TYPE_IDENTIFIER_REFRESH_TOKEN => self::GRANT_TYPE_REFRESH_TOKEN,
        self::GRANT_TYPE_IDENTIFIER_PASSWORD => self::GRANT_TYPE_PASSWORD, // Legacy Grant.
        self::GRANT_TYPE_IDENTIFIER_IMPLICIT => self::GRANT_TYPE_IMPLICIT, // Legacy Grant.
        self::GRANT_TYPE_IDENTIFIER_PERSONAL_ACCESS_TOKEN => self::GRANT_TYPE_PERSONAL_ACCESS_TOKEN, // Custom Grant.
    ];

    /**
     * Events
     */
    public const EVENT_BEFORE_CLIENT_AUTHORIZATION = 'Oauth2Server.Client.Authorization.Before';
    public const EVENT_BEFORE_AFTER_AUTHORIZATION = 'Oauth2Server.Client.Authorization.After';
    public const EVENT_BEFORE_AUTH_CODE_ISSUANCE = 'Oauth2Server.Grant.AuthCode.Issuance.Before';
    public const EVENT_AFTER_AUTH_CODE_ISSUANCE = 'Oauth2Server.Grant.AuthCode.Issuance.After';
    public const EVENT_BEFORE_ACCESS_TOKEN_ISSUANCE = 'Oauth2Server.Grant.AccessToken.Issuance.Before';
    public const EVENT_AFTER_ACCESS_TOKEN_ISSUANCE = 'Oauth2Server.Grant.AccessToken.Issuance.After';
    public const EVENT_BEFORE_REFRESH_TOKEN_ISSUANCE = 'Oauth2Server.Grant.RefreshToken.Issuance.Before';
    public const EVENT_AFTER_REFRESH_TOKEN_ISSUANCE = 'Oauth2Server.Grant.RefreshToken.Issuance.After';

    /**
     * Never show  User Account Selection for OpenID Connect.
     * @since 1.0.0
     */
    public const USER_ACCOUNT_SELECTION_DISABLED = 0;
    /**
     * Show User Account Selection upon client request for OpenID Connect.
     * @since 1.0.0
     */
    public const USER_ACCOUNT_SELECTION_UPON_CLIENT_REQUEST = 1;
    /**
     * Always show User Account Selection for OpenID Connect.
     * @since 1.0.0
     */
    public const USER_ACCOUNT_SELECTION_ALWAYS = 2;

    /**
     * Human-readable name for user account selection options.
     * @since 1.0.0
     */
    public const USER_ACCOUNT_SELECTION_NAMES = [
        self::USER_ACCOUNT_SELECTION_DISABLED => 'disabled',
        self::USER_ACCOUNT_SELECTION_UPON_CLIENT_REQUEST => 'upon_client_request',
        self::USER_ACCOUNT_SELECTION_ALWAYS => 'always',
    ];

    /**
     * Default factory interface per grant type
     * @since 1.0.0
     */
    protected const DEFAULT_GRANT_TYPE_FACTORIES = [
        self::GRANT_TYPE_AUTH_CODE => Oauth2AuthCodeGrantFactoryInterface::class,
        self::GRANT_TYPE_CLIENT_CREDENTIALS => Oauth2ClientCredentialsGrantFactoryInterface::class,
        self::GRANT_TYPE_REFRESH_TOKEN => Oauth2RefreshTokenGrantFactoryInterface::class,
        self::GRANT_TYPE_IMPLICIT => Oauth2ImplicitGrantFactoryInterface::class, // Legacy Grant.
        self::GRANT_TYPE_PASSWORD => Oauth2PasswordGrantFactoryInterface::class, // Legacy Grant.
        self::GRANT_TYPE_PERSONAL_ACCESS_TOKEN => Oauth2PersonalAccessTokenGrantFactoryInterface::class, // Custom Grant.
    ];

    /**
     * Default mapping for interfaces
     * @since 1.0.0
     */
    protected const DEFAULT_INTERFACE_IMPLEMENTATIONS = [
        # Repositories
        Oauth2AccessTokenRepositoryInterface::class => Oauth2AccessTokenRepository::class,
        Oauth2AuthCodeRepositoryInterface::class => Oauth2AuthCodeRepository::class,
        Oauth2ClientRepositoryInterface::class => Oauth2ClientRepository::class,
        Oauth2RefreshTokenRepositoryInterface::class => Oauth2RefreshTokenRepository::class,
        Oauth2ScopeRepositoryInterface::class => Oauth2ScopeRepository::class,
        Oauth2UserRepositoryInterface::class => Oauth2UserRepository::class,
        # Models
        Oauth2AccessTokenInterface::class => Oauth2AccessToken::class,
        Oauth2AccessTokenScopeInterface::class => Oauth2AccessTokenScope::class,
        Oauth2AuthCodeInterface::class => Oauth2AuthCode::class,
        Oauth2AuthCodeScopeInterface::class => Oauth2AuthCodeScope::class,
        Oauth2ClientInterface::class => Oauth2Client::class,
        Oauth2ClientScopeInterface::class => Oauth2ClientScope::class,
        Oauth2RefreshTokenInterface::class => Oauth2RefreshToken::class,
        Oauth2ScopeInterface::class => Oauth2Scope::class,
        Oauth2UserClientInterface::class => Oauth2UserClient::class,
        Oauth2UserClientScopeInterface::class => Oauth2UserClientScope::class,
        # Queries
        Oauth2AccessTokenQueryInterface::class => Oauth2AccessTokenQuery::class,
        Oauth2AccessTokenScopeQueryInterface::class => Oauth2AccessTokenScopeQuery::class,
        Oauth2AuthCodeQueryInterface::class => Oauth2AuthCodeQuery::class,
        Oauth2AuthCodeScopeQueryInterface::class => Oauth2AuthCodeScopeQuery::class,
        Oauth2ClientQueryInterface::class => Oauth2ClientQuery::class,
        Oauth2ClientScopeQueryInterface::class => Oauth2ClientScopeQuery::class,
        Oauth2RefreshTokenQueryInterface::class => Oauth2RefreshTokenQuery::class,
        Oauth2ScopeQueryInterface::class => Oauth2ScopeQuery::class,
        Oauth2UserClientQueryInterface::class => Oauth2UserClientQuery::class,
        Oauth2UserClientScopeQueryInterface::class => Oauth2UserClientScopeQuery::class,
        # Factories
        Oauth2AuthCodeGrantFactoryInterface::class => Oauth2AuthCodeGrantFactory::class,
        Oauth2ClientCredentialsGrantFactoryInterface::class => Oauth2ClientCredentialsGrantFactory::class,
        Oauth2RefreshTokenGrantFactoryInterface::class => Oauth2RefreshTokenGrantFactory::class,
        Oauth2ImplicitGrantFactoryInterface::class => Oauth2ImplicitGrantFactory::class,
        Oauth2PasswordGrantFactoryInterface::class => Oauth2PasswordGrantFactory::class,
        Oauth2PersonalAccessTokenGrantFactoryInterface::class => Oauth2PersonalAccessTokenGrantFactory::class,
        Oauth2EncryptionKeyFactoryInterface::class => Oauth2EncryptionKeyFactory::class,
        # Controllers
        Oauth2ServerControllerInterface::class => Oauth2ServerController::class,
        Oauth2ConsentControllerInterface::class => Oauth2ConsentController::class,
        Oauth2WellKnownControllerInterface::class => Oauth2WellKnownController::class,
        Oauth2CertificatesControllerInterface::class => Oauth2CertificatesController::class,
        Oauth2OidcControllerInterface::class => Oauth2OidcController::class,
        # Components (Server)
        Oauth2AuthorizationServerInterface::class => Oauth2AuthorizationServer::class,
        Oauth2ResourceServerInterface::class => Oauth2ResourceServer::class,
        # Components (Server Grants)
        Oauth2AuthCodeGrantInterface::class => Oauth2AuthCodeGrant::class,
        Oauth2ClientCredentialsGrantInterface::class => Oauth2ClientCredentialsGrant::class,
        Oauth2ImplicitGrantInterface::class => Oauth2ImplicitGrant::class,
        Oauth2PasswordGrantInterface::class => Oauth2PasswordGrant::class,
        Oauth2RefreshTokenGrantInterface::class => Oauth2RefreshTokenGrant::class,
        Oauth2PersonalAccessTokenGrantInterface::class => Oauth2PersonalAccessTokenGrant::class,
        # Components (Responses)
        Oauth2BearerTokenResponseInterface::class => Oauth2BearerTokenResponse::class,
        # Components (OpenID Connect)
        Oauth2OidcScopeCollectionInterface::class => Oauth2OidcScopeCollection::class,
        Oauth2OidcScopeInterface::class => Oauth2OidcScope::class,
        Oauth2OidcClaimInterface::class => Oauth2OidcClaim::class,
        Oauth2OidcBearerTokenResponseInterface::class => Oauth2OidcBearerTokenResponse::class,
        # Components (Misc)
        Oauth2CryptographerInterface::class => Oauth2Cryptographer::class,
        Oauth2ClientAuthorizationRequestInterface::class => Oauth2ClientAuthorizationRequest::class,
        Oauth2ScopeAuthorizationRequestInterface::class => Oauth2ScopeAuthorizationRequest::class,
    ];

    /**
     * Cache for the Repositories
     * @var Oauth2RepositoryInterface[]
     * @since 1.0.0
     */
    protected $_repositories;

    /**
     * Claims for the current request
     * @var mixed[]
     * @since 1.0.0
     */
    protected $_oauthClaims;

    /**
     * Configuration for the enabled OpenID Connect scopes.
     * @var Oauth2OidcScopeCollectionInterface|array|callable|string
     * @since 1.0.0
     */
    protected $_openIdConnectScopes = Oauth2OidcScopeCollectionInterface::OPENID_CONNECT_DEFAULT_SCOPES;

    /**
     * Cache for the OpenID Connect scope collection.
     * @var Oauth2OidcScopeCollectionInterface|null
     * @since 1.0.0
     */
    protected $_oidcScopeCollection = null;

    //////////////////////////
    /// Abstract Functions ///
    //////////////////////////

    /**
     * @return Oauth2OidcScopeCollectionInterface The supported scopes for OpenID Connect
     * @since 1.0.0
     */
    abstract public function getOidcScopeCollection();

    /**
     * Get a specific claim from an authorized Request
     * @param string $attribute
     * @param mixed|null $default
     * @return mixed|null The value of the claim or the $default value if not set.
     * @throws InvalidCallException
     * @since 1.0.0
     */
    abstract protected function getRequestOauthClaim($attribute, $default = null);


    ////////////////////////
    /// Static Functions ///
    ////////////////////////

    /**
     * Convert a grant type identifier to its numeric id
     * @param string $grantTypeIdentifier
     * @return int|null
     * @since 1.0.0
     */
    public static function getGrantTypeId($grantTypeIdentifier)
    {
        return static::GRANT_TYPE_MAPPING[$grantTypeIdentifier] ?? null;
    }

    /**
     * Convert a numeric grant type id to its string identifier
     * @param int $grantTypeId
     * @return int|null
     * @since 1.0.0
     */
    public static function getGrantTypeIdentifier($grantTypeId)
    {
        return array_flip(static::GRANT_TYPE_MAPPING)[$grantTypeId] ?? null;
    }

    /**
     * Convert Grant Type IDs to an array of their identifiers
     * @param int $grantTypeIDs
     * @return array
     */
    public static function getGrantTypeIdentifiers($grantTypeIDs)
    {
        $identifiers = [];
        foreach (static::GRANT_TYPE_MAPPING as $identifier => $id) {
            if ($grantTypeIDs & $id) {
                $identifiers[] = $identifier;
            }
        }
        return $identifiers;
    }

    /////////////////////////
    /// Getters & Setters ///
    /////////////////////////

    /**
     * @return Oauth2AccessTokenRepositoryInterface The Access Token Repository
     * @since 1.0.0
     */
    public function getAccessTokenRepository(): Oauth2AccessTokenRepositoryInterface
    {
        return $this->getRepository(Oauth2AccessTokenRepositoryInterface::class);
    }

    /**
     * @return $this
     * @since 1.0.0
     */
    public function setAccessTokenRepository(Oauth2AccessTokenRepositoryInterface $repository)
    {
        $this->setRepository(Oauth2AccessTokenRepositoryInterface::class, $repository);
        return $this;
    }

    /**
     * @return Oauth2AuthCodeRepositoryInterface The Auth Code Repository
     * @since 1.0.0
     */
    public function getAuthCodeRepository(): Oauth2AuthCodeRepositoryInterface
    {
        return $this->getRepository(Oauth2AuthCodeRepositoryInterface::class);
    }

    /**
     * @return $this
     * @since 1.0.0
     */
    public function setAuthCodeRepository(Oauth2AuthCodeRepositoryInterface $repository)
    {
        $this->setRepository(Oauth2AuthCodeRepositoryInterface::class, $repository);
        return $this;
    }

    /**
     * @return Oauth2ClientRepositoryInterface The Client Repository
     * @since 1.0.0
     */
    public function getClientRepository(): Oauth2ClientRepositoryInterface
    {
        return $this->getRepository(Oauth2ClientRepositoryInterface::class);
    }

    /**
     * @return $this
     * @since 1.0.0
     */
    public function setClientRepository(Oauth2ClientRepositoryInterface $repository)
    {
        $this->setRepository(Oauth2ClientRepositoryInterface::class, $repository);
        return $this;
    }

    /**
     * @return Oauth2RefreshTokenRepositoryInterface The Refresh Token Repository
     * @since 1.0.0
     */
    public function getRefreshTokenRepository(): Oauth2RefreshTokenRepositoryInterface
    {
        return $this->getRepository(Oauth2RefreshTokenRepositoryInterface::class);
    }

    /**
     * @return $this
     * @since 1.0.0
     */
    public function setRefreshTokenRepository(Oauth2RefreshTokenRepositoryInterface $repository)
    {
        $this->setRepository(Oauth2RefreshTokenRepositoryInterface::class, $repository);
        return $this;
    }

    /**
     * @return Oauth2ScopeRepositoryInterface The Scope Repository
     * @since 1.0.0
     */
    public function getScopeRepository(): Oauth2ScopeRepositoryInterface
    {
        return $this->getRepository(Oauth2ScopeRepositoryInterface::class);
    }

    /**
     * @return $this
     * @since 1.0.0
     */
    public function setScopeRepository(Oauth2ScopeRepositoryInterface $repository)
    {
        $this->setRepository(Oauth2ScopeRepositoryInterface::class, $repository);
        return $this;
    }

    /**
     * @return Oauth2UserRepositoryInterface The User Repository
     * @since 1.0.0
     */
    public function getUserRepository(): Oauth2UserRepositoryInterface
    {
        return $this->getRepository(Oauth2UserRepositoryInterface::class);
    }

    /**
     * @return $this
     * @since 1.0.0
     */
    public function setUserRepository(Oauth2UserRepositoryInterface $repository)
    {
        $this->setRepository(Oauth2UserRepositoryInterface::class, $repository);
        return $this;
    }

    /**
     * Get a repository by class.
     * @template T of Oauth2RepositoryInterface
     * @param class-string<T> $class
     * @return T
     * @throws \yii\base\InvalidConfigException
     * @since 1.0.0
     */
    protected function getRepository($class)
    {
        if (empty($this->_repositories[$class])) {
            $this->setRepository($class, Yii::createObject($class));
        }

        return $this->_repositories[$class];
    }

    /**
     * @param class-string<Oauth2RepositoryInterface> $class
     * @return $this
     * @throws InvalidConfigException
     */
    protected function setRepository($class, $repository)
    {
        $repository->setModule($this);
        $this->_repositories[$class] = $repository;

        return $this;
    }

    /**
     * Get the Oauth 'access_token_id' claim.
     * @return string|null
     * @see validateAuthenticatedRequest()
     * @since 1.0.0
     */
    public function getRequestOauthAccessTokenIdentifier()
    {
        return $this->getRequestOauthClaim('oauth_access_token_id');
    }

    /**
     * Get the Oauth 'client_id' claim.
     * @return string
     * @see validateAuthenticatedRequest()
     * @since 1.0.0
     */
    public function getRequestOauthClientIdentifier()
    {
        return $this->getRequestOauthClaim('oauth_client_id');
    }

    /**
     * Get the Oauth 'user_id' claim.
     * @return mixed|null
     * @see validateAuthenticatedRequest()
     * @since 1.0.0
     */
    public function getRequestOauthUserId()
    {
        return $this->getRequestOauthClaim('oauth_user_id');
    }

    /**
     * Get the Oauth 'scopes' claim.
     * @return string[]
     * @see validateAuthenticatedRequest()
     * @since 1.0.0
     */
    public function getRequestOauthScopeIdentifiers()
    {
        return $this->getRequestOauthClaim('oauth_scopes', []);
    }

    /**
     * Check if the Request has the specified scope.
     * @param string $scopeIdentifier
     * @param bool $strict If strict is `false` and the user is not authenticated via Oauth, return true.
     * @return bool
     * @see validateAuthenticatedRequest()
     * @since 1.0.0
     */
    public function requestHasScope($scopeIdentifier, $strict = true)
    {
        if (!$strict && ($this->getRequestOauthAccessTokenIdentifier() === null)) {
            //If not strict and the user is not authenticated via Oauth, allow the scope.
            return true;
        }
        return in_array($scopeIdentifier, $this->getRequestOauthScopeIdentifiers());
    }

    /**
     * Get the configuration for the enabled OpenID Connect scopes.
     * @return Oauth2OidcScopeCollectionInterface|array|callable|string
     * @see getOidcScopeCollection()
     * @since 1.0.0
     */
    public function getOpenIdConnectScopes()
    {
        return $this->_openIdConnectScopes;
    }

    /**
     * Set the configuration for the enabled OpenID Connect scopes.
     * @return $this
     * @see getOidcScopeCollection()
     * @since 1.0.0
     */
    public function setOpenIdConnectScopes($openIdConnectScopes)
    {
        $this->_openIdConnectScopes = $openIdConnectScopes;
        $this->_oidcScopeCollection = null;
        return $this;
    }

    ////////////////////////
    /// Public Functions ///
    ////////////////////////

    /**
     * Generates a JWT 'id_token' for OpenID Connect
     * @param Oauth2OidcUserInterface $user
     * @param string $clientIdentifier
     * @param CryptKey $privateKey
     * @param string[] $scopeIdentifiers
     * @param string|null $nonce
     * @param \DateTimeImmutable|null $expiryDateTime
     * @return \Lcobucci\JWT\Token\Plain
     * @throws InvalidConfigException
     * @see getOidcScopeCollection()
     */
    public function generateOpenIdConnectUserClaimsToken(
        $user,
        $clientIdentifier,
        $privateKey,
        $scopeIdentifiers,
        $nonce = null,
        $expiryDateTime = null
    ) {
        if (!($user instanceof Oauth2OidcUserInterface)) {
            throw new InvalidConfigException('In order to support OpenID Connect '
                . get_class($user) . ' must implement ' . Oauth2OidcUserInterface::class);
        }

        $jwtConfiguration = Configuration::forAsymmetricSigner(
            new Sha256(),
            InMemory::plainText($privateKey->getKeyContents(), $privateKey->getPassPhrase() ?? ''),
            InMemory::empty(),
        );

        $builder = $jwtConfiguration->builder()
            ->permittedFor($clientIdentifier)
            ->issuedBy(Yii::$app->request->hostInfo)
            ->issuedAt(new \DateTimeImmutable())
            ->relatedTo((string)$user->getIdentifier())
            ->withClaim(
                Oauth2OidcClaimInterface::OPENID_CONNECT_CLAIM_AUTH_TIME,
                $user->getLatestAuthenticatedAt()->getTimestamp()
            );

        if ($nonce) {
            $builder->withClaim(Oauth2OidcClaimInterface::OPENID_CONNECT_CLAIM_NONCE, $nonce);
        }

        if ($expiryDateTime) {
            $builder->expiresAt($expiryDateTime);
        }

        $oidcScopeCollection = $this->getOidcScopeCollection();

        $claims = $oidcScopeCollection->getFilteredClaims($scopeIdentifiers);

        foreach ($claims as $claim) {
            if (
                in_array(
                    $claim->getIdentifier(),
                    Oauth2OidcScopeInterface::OPENID_CONNECT_DEFAULT_SCOPE_CLAIMS[
                        Oauth2OidcScopeInterface::OPENID_CONNECT_SCOPE_OPENID
                    ]
                )
            ) {
                // Skip default claims for OpenID (already set above).
                continue;
            }
            $claimValue = $user->getOpenIdConnectClaimValue($claim, $this);
            $builder->withClaim($claim->getIdentifier(), $claimValue);
        }

        return $builder->getToken($jwtConfiguration->signer(), $jwtConfiguration->signingKey());
    }
}
