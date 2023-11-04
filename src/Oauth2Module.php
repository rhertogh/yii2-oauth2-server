<?php

/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace rhertogh\Yii2Oauth2Server;

use Defuse\Crypto\Exception\BadFormatException;
use Defuse\Crypto\Exception\EnvironmentIsBrokenException;
use GuzzleHttp\Psr7\Response as Psr7Response;
use GuzzleHttp\Psr7\ServerRequest as Psr7ServerRequest;
use League\OAuth2\Server\CryptKey;
use League\OAuth2\Server\Grant\GrantTypeInterface;
use rhertogh\Yii2Oauth2Server\base\Oauth2BaseModule;
use rhertogh\Yii2Oauth2Server\components\server\tokens\Oauth2AccessTokenData;
use rhertogh\Yii2Oauth2Server\controllers\console\Oauth2ClientController;
use rhertogh\Yii2Oauth2Server\controllers\console\Oauth2DebugController;
use rhertogh\Yii2Oauth2Server\controllers\console\Oauth2EncryptionController;
use rhertogh\Yii2Oauth2Server\controllers\console\Oauth2MigrationsController;
use rhertogh\Yii2Oauth2Server\controllers\console\Oauth2PersonalAccessTokenController;
use rhertogh\Yii2Oauth2Server\exceptions\Oauth2ServerException;
use rhertogh\Yii2Oauth2Server\helpers\DiHelper;
use rhertogh\Yii2Oauth2Server\helpers\Psr7Helper;
use rhertogh\Yii2Oauth2Server\interfaces\components\authorization\Oauth2ClientAuthorizationRequestInterface;
use rhertogh\Yii2Oauth2Server\interfaces\components\common\DefaultAccessTokenTtlInterface;
use rhertogh\Yii2Oauth2Server\interfaces\components\encryption\Oauth2CryptographerInterface;
use rhertogh\Yii2Oauth2Server\interfaces\components\factories\encryption\Oauth2EncryptionKeyFactoryInterface;
use rhertogh\Yii2Oauth2Server\interfaces\components\factories\grants\base\Oauth2GrantTypeFactoryInterface;
use rhertogh\Yii2Oauth2Server\interfaces\components\openidconnect\scope\Oauth2OidcScopeCollectionInterface;
use rhertogh\Yii2Oauth2Server\interfaces\components\openidconnect\server\Oauth2OidcBearerTokenResponseInterface;
use rhertogh\Yii2Oauth2Server\interfaces\components\server\Oauth2AuthorizationServerInterface;
use rhertogh\Yii2Oauth2Server\interfaces\components\server\Oauth2ResourceServerInterface;
use rhertogh\Yii2Oauth2Server\interfaces\controllers\web\Oauth2CertificatesControllerInterface;
use rhertogh\Yii2Oauth2Server\interfaces\controllers\web\Oauth2ConsentControllerInterface;
use rhertogh\Yii2Oauth2Server\interfaces\controllers\web\Oauth2OidcControllerInterface;
use rhertogh\Yii2Oauth2Server\interfaces\controllers\web\Oauth2ServerControllerInterface;
use rhertogh\Yii2Oauth2Server\interfaces\controllers\web\Oauth2WellKnownControllerInterface;
use rhertogh\Yii2Oauth2Server\interfaces\filters\auth\Oauth2HttpBearerAuthInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\base\Oauth2EncryptedStorageInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\external\user\Oauth2OidcUserInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\external\user\Oauth2UserInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\Oauth2ClientInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\Oauth2ScopeInterface;
use rhertogh\Yii2Oauth2Server\traits\DefaultAccessTokenTtlTrait;
use Yii;
use yii\base\BootstrapInterface;
use yii\base\InvalidArgumentException;
use yii\base\InvalidCallException;
use yii\base\InvalidConfigException;
use yii\console\Application as ConsoleApplication;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use yii\helpers\StringHelper;
use yii\i18n\PhpMessageSource;
use yii\validators\IpValidator;
use yii\web\Application as WebApplication;
use yii\web\GroupUrlRule;
use yii\web\IdentityInterface;
use yii\web\Response;
use yii\web\UrlRule;

/**
 * This is the main module class for the Yii2 Oauth2 Server module.
 * To use it, include it as a module in the application configuration like the following:
 *
 * ~~~
 * return [
 *     'bootstrap' => ['oauth2'],
 *     'modules' => [
 *         'oauth2' => [
 *             'class' => 'rhertogh\Yii2Oauth2Server\Oauth2Module',
 *             // ... Please check docs/guide/start-installation.md further details
 *          ],
 *     ],
 * ]
 * ~~~
 *
 * @property \DateInterval|string|null $defaultAccessTokenTTL
 * @since 1.0.0
 */
class Oauth2Module extends Oauth2BaseModule implements BootstrapInterface, DefaultAccessTokenTtlInterface
{
    use DefaultAccessTokenTtlTrait;

    /**
     * Application type "web": http response.
     * @since 1.0.0
     */
    public const APPLICATION_TYPE_WEB = 'web';
    /**
     * Application type "console": cli response.
     * @since 1.0.0
     */
    public const APPLICATION_TYPE_CONSOLE = 'console';
    /**
     * Supported Application types.
     * @since 1.0.0
     */
    public const APPLICATION_TYPES = [
        self::APPLICATION_TYPE_WEB,
        self::APPLICATION_TYPE_CONSOLE,
    ];

    /**
     * "Authorization Server" Role, please see guide for details.
     * @since 1.0.0
     */
    public const SERVER_ROLE_AUTHORIZATION_SERVER = 1;
    /**
     * "Resource Server" Role, please see guide for details.
     * @since 1.0.0
     */
    public const SERVER_ROLE_RESOURCE_SERVER = 2;

    /**
     * Required settings when the server role includes Authorization Server
     * @since 1.0.0
     */
    protected const REQUIRED_SETTINGS_AUTHORIZATION_SERVER = [
        'codesEncryptionKey',
        'storageEncryptionKeys',
        'defaultStorageEncryptionKey',
        'privateKey',
        'publicKey',
    ];

    /**
     * Encrypted Models
     *
     * @since 1.0.0
     */
    protected const ENCRYPTED_MODELS = [
        Oauth2ClientInterface::class,
    ];

    /**
     * Required settings when the server role includes Resource Server
     * @since 1.0.0
     */
    protected const REQUIRED_SETTINGS_RESOURCE_SERVER = [
        'publicKey',
    ];

    /**
     * Prefix used in session storage of Client Authorization Requests
     * @since 1.0.0
     */
    protected const CLIENT_AUTHORIZATION_REQUEST_SESSION_PREFIX = 'OATH2_CLIENT_AUTHORIZATION_REQUEST_';

    /**
     * Controller mapping for the module. Will be parsed on `init()`.
     * @since 1.0.0
     */
    protected const CONTROLLER_MAP = [
        self::APPLICATION_TYPE_WEB => [
            Oauth2ServerControllerInterface::CONTROLLER_NAME => [
                'controller' => Oauth2ServerControllerInterface::class,
                'serverRole' => self::SERVER_ROLE_AUTHORIZATION_SERVER,
            ],
            Oauth2ConsentControllerInterface::CONTROLLER_NAME => [
                'controller' => Oauth2ConsentControllerInterface::class,
                'serverRole' => self::SERVER_ROLE_AUTHORIZATION_SERVER,
            ],
            Oauth2WellKnownControllerInterface::CONTROLLER_NAME => [
                'controller' => Oauth2WellKnownControllerInterface::class,
                'serverRole' => self::SERVER_ROLE_AUTHORIZATION_SERVER,
            ],
            Oauth2CertificatesControllerInterface::CONTROLLER_NAME => [
                'controller' => Oauth2CertificatesControllerInterface::class,
                'serverRole' => self::SERVER_ROLE_AUTHORIZATION_SERVER,
            ],
            Oauth2OidcControllerInterface::CONTROLLER_NAME => [
                'controller' => Oauth2OidcControllerInterface::class,
                'serverRole' => self::SERVER_ROLE_AUTHORIZATION_SERVER,
            ],
        ],
        self::APPLICATION_TYPE_CONSOLE => [
            'migrations' => [
                'controller' => Oauth2MigrationsController::class,
                'serverRole' => self::SERVER_ROLE_AUTHORIZATION_SERVER | self::SERVER_ROLE_RESOURCE_SERVER,
            ],
            'client' => [
                'controller' => Oauth2ClientController::class,
                'serverRole' => self::SERVER_ROLE_AUTHORIZATION_SERVER,
            ],
            'encryption' => [
                'controller' => Oauth2EncryptionController::class,
                'serverRole' => self::SERVER_ROLE_AUTHORIZATION_SERVER,
            ],
            'debug' => [
                'controller' => Oauth2DebugController::class,
                'serverRole' => self::SERVER_ROLE_AUTHORIZATION_SERVER | self::SERVER_ROLE_RESOURCE_SERVER,
            ],
            'pat' => [
                'controller' => Oauth2PersonalAccessTokenController::class,
                'serverRole' => self::SERVER_ROLE_AUTHORIZATION_SERVER,
            ]
        ]
    ];

    /**
     * @inheritdoc
     */
    public $controllerNamespace = __NAMESPACE__ . '\-'; // Set explicitly via $controllerMap in `init()`.

    /**
     * @var string|null The application type. If `null` the type will be automatically detected.
     * @see APPLICATION_TYPES
     */
    public $appType = null;

    /**
     * @var int The Oauth 2.0 Server Roles the module will perform.
     * @since 1.0.0
     */
    public $serverRole = self::SERVER_ROLE_AUTHORIZATION_SERVER | self::SERVER_ROLE_RESOURCE_SERVER;

    /**
     * @var string|null The private key for the server. Can be a string containing the key itself or point to a file.
     * When pointing to a file it's recommended to use an absolute path prefixed with 'file://' or start with
     * '@' to use a Yii path alias.
     * @see $privateKeyPassphrase For setting a passphrase for the private key.
     * @since 1.0.0
     */
    public $privateKey = null;

    /**
     * @var string|null The passphrase for the private key.
     * @since 1.0.0
     */
    public $privateKeyPassphrase = null;
    /**
     * @var string|null The public key for the server. Can be a string containing the key itself or point to a file.
     * When pointing to a file it's recommended to use an absolute path prefixed with 'file://' or start with
     * '@' to use a Yii path alias.
     * @since 1.0.0
     */
    public $publicKey = null;

    /**
     * @var string|null The encryption key for authorization and refresh codes.
     * @since 1.0.0
     */
    public $codesEncryptionKey = null;

    /**
     * @var string[]|string|null The encryption keys for storage like client secrets.
     * Where the array key is the name of the key, and the value the key itself. E.g.
     * `['2022-01-01' => 'def00000cb36fd6ed6641e0ad70805b28d....']`
     * If a string (instead of an array of strings) is specified it will be JSON decoded
     * it should contain an object where each property name is the name of the key, its value the key itself. E.g.
     * `{"2022-01-01": "def00000cb36fd6ed6641e0ad70805b28d...."}`
     *
     * @since 1.0.0
     */
    public $storageEncryptionKeys = null;

    /**
     * @var string|null The index of the default key in storageEncryptionKeys. E.g. 'myKey'.
     * @since 1.0.0
     */
    public $defaultStorageEncryptionKey = null;

    /**
     * @var string|string[]|null IP addresses, CIDR ranges, or range aliases that are allowed to connect over a
     * non-TLS connection. If `null` or an empty array LTS is always required.
     *
     * Warning: Although you can use '*' or 'any' to allow a non-TLS connection from any ip address,
     * doing so would most likely introduce a security risk and should be done for debugging purposes only!
     *
     * @see \yii\validators\IpValidator::$networks for a list of available alliasses.
     */
    public $nonTlsAllowedRanges = 'localhost';

    /**
     * @var class-string<Oauth2UserInterface>|null The Identity Class of your application,
     * most likely the same as the 'identityClass' of your application's User Component.
     * @since 1.0.0
     */
    public $identityClass = null;

    /**
     * @var null|string Prefix used for url rules. When `null` the module's uniqueId will be used.
     * @since 1.0.0
     */
    public $urlRulesPrefix = null;

    /**
     * @var string URL path for the access token endpoint (will be prefixed with $urlRulesPrefix).
     * @since 1.0.0
     */
    public $authorizePath = 'authorize';

    /**
     * @var string URL path for the access token endpoint (will be prefixed with $urlRulesPrefix).
     * @since 1.0.0
     */
    public $accessTokenPath = 'access-token';

    /**
     * @var string URL path for the certificates jwks endpoint (will be prefixed with $urlRulesPrefix).
     * @since 1.0.0
     */
    public $jwksPath = 'certs';

    /**
     * The URL to the page where the user can perform the client/scope authorization
     * (if `null` the build in page will be used).
     * @return string
     * @since 1.0.0
     */
    public $clientAuthorizationUrl = null;

    /**
     * @var string The URL path to the build in page where the user can authorize the client for the requested scopes
     * (will be prefixed with $urlRulesPrefix).
     * Note: This setting will only be used if $clientAuthorizationUrl is `null`.
     * @since 1.0.0
     */
    public $clientAuthorizationPath = 'authorize-client';

    /**
     * @var string The view to use in the "client authorization action" for the page where the user can
     * authorize the client for the requested scopes.
     * Note: This setting will only be used if $clientAuthorizationUrl is `null`.
     * @since 1.0.0
     */
    public $clientAuthorizationView = 'authorize-client';

    /**
     * Configuration for `Oauth2Client::getRedirectUrisEnvVarConfig()` fallback (the
     * Oauth2Client::$envVarConfig['redirectUris'] has precedence).
     * When configured, environment variables specified in the `Oauth2Client` redirect URI(s) will be substituted with
     * their values. Please see `EnvironmentHelper::parseEnvVars()` for more details.
     *
     * Warning: This setting applies to all clients, for security it's recommended to specify this configuration at the
     * individual client level via its `envVarConfig` setting.
     *
     * @var array{
     *          allowList: array,
     *          denyList: array|null,
     *          parseNested: bool,
     *          exceptionWhenNotSet: bool,
     *          exceptionWhenNotAllowed: bool,
     *      }|null
     * @see Oauth2ClientInterface::setEnvVarConfig()
     * @see Oauth2ClientInterface::getRedirectUrisEnvVarConfig()
     * @see \rhertogh\Yii2Oauth2Server\helpers\EnvironmentHelper::parseEnvVars()
     */
    public $clientRedirectUrisEnvVarConfig = null;

    /**
     * @var string|null The URL path to the OpenID Connect Provider Configuration Information Action.
     * If set to `null` the endpoint will be disabled.
     * Note: This path is defined in the
     *       [OpenID Connect Discovery](https://openid.net/specs/openid-connect-discovery-1_0.html#rfc.section.4)
     *       specification and should normally not be changed.
     * @since 1.0.0
     */
    public $openIdConnectProviderConfigurationInformationPath = '.well-known/openid-configuration';

    /**
     * @var string The URL path to the OpenID Connect Userinfo Action (will be prefixed with $urlRulesPrefix).
     * Note: This setting will only be used if $enableOpenIdConnect and $openIdConnectUserinfoEndpoint are `true`.
     * @since 1.0.0
     */
    public $openIdConnectUserinfoPath = 'oidc/userinfo';

    /**
     * @var Oauth2GrantTypeFactoryInterface[]|GrantTypeInterface[]|string[]|Oauth2GrantTypeFactoryInterface|GrantTypeInterface|string|callable
     * The Oauth 2.0 Grant Types that the module will serve.
     * @since 1.0.0
     */
    public $grantTypes = [];

    /**
     * @var bool Should the resource server check for revocation of the access token.
     * @since 1.0.0
     */
    public $resourceServerAccessTokenRevocationValidation = true;

    /**
     * @var bool Enable support for OpenIdvConnect.
     * @since 1.0.0
     */
    public $enableOpenIdConnect = false;

    /**
     * @var bool Enable the .well-known/openid-configuration discovery endpoint.
     * @since 1.0.0
     */
    public $enableOpenIdConnectDiscovery = true;

    /**
     * @var bool include `grant_types_supported` in the OpenIdConnect Discovery.
     * Note: Since grant types can be specified per client not all clients might support all enabled grant types.
     * @since 1.0.0
     */
    public $openIdConnectDiscoveryIncludeSupportedGrantTypes = true;

    /**
     * @var string URL to include in the OpenID Connect Discovery Service of a page containing
     * human-readable information that developers might want or need to know when using the OpenID Provider.
     * @see 'service_documentation' in https://openid.net/specs/openid-connect-discovery-1_0.html#rfc.section.3
     * @since 1.0.0
     */
    public $openIdConnectDiscoveryServiceDocumentationUrl = null;

    /**
     * @var string|bool A string to a custom userinfo endpoint or `true` to enable the build in endpoint.
     * @since 1.0.0
     */
    public $openIdConnectUserinfoEndpoint = true;

    /**
     * Warning! Enabling this setting might introduce privacy concerns since the client could poll for the
     * online status of a user.
     *
     * @var bool If this setting is disabled in case of OpenID Connect Context the Access Token won't include a
     * Refresh Token when the 'offline_access' scope is not included in the authorization request.
     * In some cases it might be needed to always include a Refresh Token, in that case enable this setting and
     * implement the `Oauth2OidcUserSessionStatusInterface` on the User Identity model.
     * @since 1.0.0
     */
    public $openIdConnectIssueRefreshTokenWithoutOfflineAccessScope = false;

    /**
     * @var int The default option for "User Account Selection' when not specified for a client.
     * @since 1.0.0
     */
    public $defaultUserAccountSelection = self::USER_ACCOUNT_SELECTION_DISABLED;

    /**
     * @var bool|null Display exception messages that might leak server details. This could be useful for debugging.
     * In case of `null` (default) the YII_DEBUG constant will be used.
     * Warning: Should NOT be enabled in production!
     * @since 1.0.0
     */
    public $displayConfidentialExceptionMessages = null;

    /**
     * @var string|null The namespace with which migrations will be created (and by which they will be located).
     * Note: The specified namespace must be defined as a Yii alias (e.g. '@app').
     * @since 1.0.0
     */
    public $migrationsNamespace = null;
    /**
     * @var string|null Optional prefix used in the name of generated migrations
     * @since 1.0.0
     */
    public $migrationsPrefix = null;
    /**
     * @var string|array|int|null Sets the file ownership of generated migrations
     * @see \yii\helpers\BaseFileHelper::changeOwnership()
     * @since 1.0.0
     */
    public $migrationsFileOwnership = null;
    /**
     * @var int|null Sets the file mode of generated migrations
     * @see \yii\helpers\BaseFileHelper::changeOwnership()
     * @since 1.0.0
     */
    public $migrationsFileMode = null;

    /**
     * @var Oauth2AuthorizationServerInterface|null Cache for the authorization server
     * @since 1.0.0
     */
    protected $_authorizationServer = null;

    /**
     * @var Oauth2ResourceServerInterface|null Cache for the resource server
     * @since 1.0.0
     */
    protected $_resourceServer = null;

    /**
     * @var Oauth2CryptographerInterface|null Cache for the Oauth2Cryptographer
     * @since 1.0.0
     */
    protected $_cryptographer = null;

    /**
     * @var string|null The authorization header used when the authorization request was validated.
     * @since 1.0.0
     */
    protected $_oauthClaimsAuthorizationHeader = null;

    /**
     * @inheritDoc
     * @throws InvalidConfigException
     */
    public function init()
    {
        parent::init();

        $app = Yii::$app;

        if ($app instanceof WebApplication || $this->appType == static::APPLICATION_TYPE_WEB) {
            $controllerMap = static::CONTROLLER_MAP[static::APPLICATION_TYPE_WEB];
        } elseif ($app instanceof ConsoleApplication || $this->appType == static::APPLICATION_TYPE_CONSOLE) {
            $controllerMap = static::CONTROLLER_MAP[static::APPLICATION_TYPE_CONSOLE];
        } else {
            throw new InvalidConfigException(
                'Unable to detect application type, configure it manually by setting `$appType`.'
            );
        }
        $controllerMap = array_filter(
            $controllerMap,
            fn($controllerSettings) => $controllerSettings['serverRole'] & $this->serverRole
        );
        $this->controllerMap = ArrayHelper::getColumn($controllerMap, 'controller');

        if (empty($this->identityClass)) {
            throw new InvalidConfigException('$identityClass must be set.');
        } elseif (!is_a($this->identityClass, Oauth2UserInterface::class, true)) {
            throw new InvalidConfigException(
                $this->identityClass . ' must implement ' . Oauth2UserInterface::class
            );
        }

        foreach (static::DEFAULT_INTERFACE_IMPLEMENTATIONS as $interface => $implementation) {
            if (!Yii::$container->has($interface)) {
                Yii::$container->set($interface, $implementation);
            }
        }

        if (empty($this->urlRulesPrefix)) {
            $this->urlRulesPrefix = $this->uniqueId;
        }

        $this->registerTranslations();
    }

    /**
     * @inheritdoc
     * @throws InvalidConfigException
     */
    public function bootstrap($app)
    {
        if (
            $app instanceof WebApplication
            && $this->serverRole & static::SERVER_ROLE_AUTHORIZATION_SERVER
        ) {
            $rules = [
                $this->accessTokenPath => Oauth2ServerControllerInterface::CONTROLLER_NAME
                    . '/' . Oauth2ServerControllerInterface::ACTION_NAME_ACCESS_TOKEN,
                $this->authorizePath => Oauth2ServerControllerInterface::CONTROLLER_NAME
                    . '/' . Oauth2ServerControllerInterface::ACTION_NAME_AUTHORIZE,
                $this->jwksPath => Oauth2CertificatesControllerInterface::CONTROLLER_NAME
                    . '/' . Oauth2CertificatesControllerInterface::ACTION_NAME_JWKS,
            ];

            if (empty($this->clientAuthorizationUrl)) {
                $rules[$this->clientAuthorizationPath] = Oauth2ConsentControllerInterface::CONTROLLER_NAME
                    . '/' . Oauth2ConsentControllerInterface::ACTION_NAME_AUTHORIZE_CLIENT;
            }

            if ($this->enableOpenIdConnect && $this->openIdConnectUserinfoEndpoint === true) {
                $rules[$this->openIdConnectUserinfoPath] =
                    Oauth2OidcControllerInterface::CONTROLLER_NAME
                    . '/' . Oauth2OidcControllerInterface::ACTION_NAME_USERINFO;
            }

            $urlManager = $app->getUrlManager();
            $urlManager->addRules([
                Yii::createObject([
                    'class' => GroupUrlRule::class,
                    'prefix' => $this->urlRulesPrefix,
                    'routePrefix' => $this->id,
                    'rules' => $rules,
                ]),
            ]);

            if (
                $this->enableOpenIdConnect
                && $this->enableOpenIdConnectDiscovery
                && $this->openIdConnectProviderConfigurationInformationPath
            ) {
                $urlManager->addRules([
                    Yii::createObject([
                        'class' => UrlRule::class,
                        'pattern' => $this->openIdConnectProviderConfigurationInformationPath,
                        'route' => $this->id
                            . '/' . Oauth2WellKnownControllerInterface::CONTROLLER_NAME
                            . '/' . Oauth2WellKnownControllerInterface::ACTION_NAME_OPENID_CONFIGURATION,
                    ]),
                ]);
            }
        }
    }

    /**
     * Registers the translations for the module
     * @param bool $force Force the setting of the translations (even if they are already defined).
     * @since 1.0.0
     */
    public function registerTranslations($force = false)
    {
        if ($force || !array_key_exists('oauth2', Yii::$app->i18n->translations)) {
            Yii::$app->i18n->translations['oauth2'] = [
                'class' => PhpMessageSource::class,
                'sourceLanguage' => 'en-US',
                'basePath' => __DIR__ . DIRECTORY_SEPARATOR . 'messages',
                'fileMap' => [
                    'oauth2' => 'oauth2.php',
                ],
            ];
        }
    }

    /**
     * @param string $identifier The client identifier
     * @param string $name The (user-friendly) name of the client
     * @param int $grantTypes The grant types enabled for this client.
     *        Use bitwise `OR` to combine multiple types,
     *        e.g. `Oauth2Module::GRANT_TYPE_AUTH_CODE | Oauth2Module::GRANT_TYPE_REFRESH_TOKEN`
     * @param string|string[] $redirectURIs One or multiple redirect URIs for the client
     * @param int $type The client type (e.g. Confidential or Public)
     *        See `\rhertogh\Yii2Oauth2Server\interfaces\models\Oauth2ClientInterface::TYPES` for possible values
     * @param string|null $secret The client secret in case the client `type` is `confidential`.
     * @param string|string[]|array[]|Oauth2ScopeInterface[]|null $scopes
     * @param int|null $userId
     * @return Oauth2ClientInterface
     * @throws InvalidConfigException
     * @throws \yii\db\Exception
     */
    public function createClient(
        $identifier,
        $name,
        $grantTypes,
        $redirectURIs,
        $type,
        $secret = null,
        $scopes = null,
        $userId = null,
        $endUsersMayAuthorizeClient = null,
        $skipAuthorizationIfScopeIsAllowed = null
    ) {
        if (!($this->serverRole & static::SERVER_ROLE_AUTHORIZATION_SERVER)) {
            throw new InvalidCallException('Oauth2 server role does not include authorization server.');
        }

        /** @var Oauth2ClientInterface $client */
        $client = Yii::createObject([
            'class' => Oauth2ClientInterface::class,
            'identifier' => $identifier,
            'type' => $type,
            'name' => $name,
            'redirectUri' => $redirectURIs,
            'grantTypes' => $grantTypes,
            'endUsersMayAuthorizeClient' => $endUsersMayAuthorizeClient,
            'skip_authorization_if_scope_is_allowed' => $skipAuthorizationIfScopeIsAllowed,
            'clientCredentialsGrantUserId' => $userId
        ]);

        $transaction = $client::getDb()->beginTransaction();

        try {
            if ($type == Oauth2ClientInterface::TYPE_CONFIDENTIAL) {
                $client->setSecret($secret, $this->getCryptographer());
            }

            $client
                ->persist()
                ->syncClientScopes($scopes, $this->getScopeRepository());

            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }

        return $client;
    }

    /**
     * @return CryptKey The private key of the server.
     * @throws InvalidConfigException
     * @since 1.0.0
     */
    public function getPrivateKey()
    {
        $privateKey = $this->privateKey;
        if (StringHelper::startsWith($privateKey, '@')) {
            $privateKey = 'file://' . Yii::getAlias($privateKey);
        }
        return Yii::createObject(CryptKey::class, [$privateKey, $this->privateKeyPassphrase]);
    }

    /**
     * @return CryptKey The public key of the server.
     * @throws InvalidConfigException
     * @since 1.0.0
     */
    public function getPublicKey()
    {
        $publicKey = $this->publicKey;
        if (StringHelper::startsWith($publicKey, '@')) {
            $publicKey = 'file://' . Yii::getAlias($publicKey);
        }
        return Yii::createObject(CryptKey::class, [$publicKey]);
    }

    /**
     * @return Oauth2AuthorizationServerInterface The authorization server.
     * @throws InvalidConfigException
     * @since 1.0.0
     */
    public function getAuthorizationServer()
    {
        if (!($this->serverRole & static::SERVER_ROLE_AUTHORIZATION_SERVER)) {
            throw new InvalidCallException('Oauth2 server role does not include authorization server.');
        }

        if (!$this->_authorizationServer) {
            $this->ensureProperties(static::REQUIRED_SETTINGS_AUTHORIZATION_SERVER);

            if (!$this->getCryptographer()->hasKey($this->defaultStorageEncryptionKey)) {
                throw new InvalidConfigException(
                    'Key "' . $this->defaultStorageEncryptionKey . '" is not set in $storageEncryptionKeys'
                );
            }

            /** @var Oauth2EncryptionKeyFactoryInterface $keyFactory */
            $keyFactory = Yii::createObject(Oauth2EncryptionKeyFactoryInterface::class);
            try {
                $codesEncryptionKey = $keyFactory->createFromAsciiSafeString($this->codesEncryptionKey);
            } catch (BadFormatException $e) {
                throw new InvalidConfigException(
                    '$codesEncryptionKey is malformed: ' . $e->getMessage(),
                    0,
                    $e
                );
            } catch (EnvironmentIsBrokenException $e) {
                throw new InvalidConfigException(
                    'Could not instantiate $codesEncryptionKey: ' . $e->getMessage(),
                    0,
                    $e
                );
            }

            $responseType = null;
            if ($this->enableOpenIdConnect) {
                $responseType = Yii::createObject(Oauth2OidcBearerTokenResponseInterface::class, [
                    $this,
                ]);
            }

            $this->_authorizationServer = Yii::createObject(Oauth2AuthorizationServerInterface::class, [
                $this->getClientRepository(),
                $this->getAccessTokenRepository(),
                $this->getScopeRepository(),
                $this->getPrivateKey(),
                $codesEncryptionKey,
                $responseType
            ]);

            if (!empty($this->grantTypes)) {
                $grantTypes = $this->grantTypes;

                if (is_callable($grantTypes)) {
                    call_user_func($grantTypes, $this->_authorizationServer, $this);
                } else {
                    if (!is_array($grantTypes)) {
                        $grantTypes = [$grantTypes];
                    }

                    foreach ($grantTypes as $grantTypeDefinition) {
                        if ($grantTypeDefinition instanceof GrantTypeInterface) {
                            $accessTokenTTL = $this->getDefaultAccessTokenTTL();
                            $this->_authorizationServer->enableGrantType($grantTypeDefinition, $accessTokenTTL);
                        } elseif (
                            (
                                is_numeric($grantTypeDefinition)
                                && array_key_exists($grantTypeDefinition, static::DEFAULT_GRANT_TYPE_FACTORIES)
                            )
                            || is_a($grantTypeDefinition, Oauth2GrantTypeFactoryInterface::class, true)
                        ) {
                            if (
                                is_numeric($grantTypeDefinition)
                                && array_key_exists($grantTypeDefinition, static::DEFAULT_GRANT_TYPE_FACTORIES)
                            ) {
                                $grantTypeDefinition = static::DEFAULT_GRANT_TYPE_FACTORIES[$grantTypeDefinition];
                            }

                            /** @var Oauth2GrantTypeFactoryInterface $factory */
                            $factory = Yii::createObject([
                                'class' => $grantTypeDefinition,
                                'module' => $this,
                            ]);
                            $accessTokenTTL = $factory->getDefaultAccessTokenTTL()
                                ?? $this->getDefaultAccessTokenTTL();
                            $this->_authorizationServer->enableGrantType($factory->getGrantType(), $accessTokenTTL);
                        } else {
                            throw new InvalidConfigException(
                                'Unknown grantType '
                                . (
                                    is_scalar($grantTypeDefinition)
                                        ? '"' . $grantTypeDefinition . '".'
                                        : 'with data type ' . gettype($grantTypeDefinition)
                                )
                            );
                        }
                    }
                }
            }
        }

        return $this->_authorizationServer;
    }

    /**
     * @inheritDoc
     * @throws InvalidConfigException
     */
    public function getOidcScopeCollection()
    {
        if ($this->_oidcScopeCollection === null) {
            $openIdConnectScopes = $this->getOpenIdConnectScopes();
            if ($openIdConnectScopes instanceof Oauth2OidcScopeCollectionInterface) {
                $this->_oidcScopeCollection = $openIdConnectScopes;
            } elseif (is_callable($openIdConnectScopes)) {
                $this->_oidcScopeCollection = call_user_func($openIdConnectScopes, $this);
                if (!($this->_oidcScopeCollection instanceof Oauth2OidcScopeCollectionInterface)) {
                    throw new InvalidConfigException(
                        '$openIdConnectScopes must return an instance of '
                            . Oauth2OidcScopeCollectionInterface::class
                    );
                }
            } elseif (is_array($openIdConnectScopes) || is_string($openIdConnectScopes)) {
                $this->_oidcScopeCollection = Yii::createObject([
                    'class' => Oauth2OidcScopeCollectionInterface::class,
                    'oidcScopes' => (array)$openIdConnectScopes,
                ]);
            } else {
                throw new InvalidConfigException(
                    '$openIdConnectScopes must be a callable, array, string or '
                        . Oauth2OidcScopeCollectionInterface::class
                );
            }
        }

        return $this->_oidcScopeCollection;
    }

    /**
     * @return Oauth2ResourceServerInterface The resource server.
     * @throws InvalidConfigException
     * @since 1.0.0
     */
    public function getResourceServer()
    {
        if (!($this->serverRole & static::SERVER_ROLE_RESOURCE_SERVER)) {
            throw new InvalidCallException('Oauth2 server role does not include resource server.');
        }

        if (!$this->_resourceServer) {
            $this->ensureProperties(static::REQUIRED_SETTINGS_RESOURCE_SERVER);

            $accessTokenRepository = $this->getAccessTokenRepository()
                ->setRevocationValidation($this->resourceServerAccessTokenRevocationValidation);

            $this->_resourceServer = Yii::createObject(Oauth2ResourceServerInterface::class, [
                $accessTokenRepository,
                $this->getPublicKey(),
            ]);
        }

        return $this->_resourceServer;
    }

    /**
     * @return Oauth2CryptographerInterface The data cryptographer for the module.
     * @throws InvalidConfigException
     * @since 1.0.0
     */
    public function getCryptographer()
    {
        if (!$this->_cryptographer) {
            $this->_cryptographer = Yii::createObject([
                'class' => Oauth2CryptographerInterface::class,
                'keys' => $this->storageEncryptionKeys,
                'defaultKeyName' => $this->defaultStorageEncryptionKey,
            ]);
        }

        return $this->_cryptographer;
    }

    /**
     * @param string|null $newKeyName
     * @return array
     * @throws InvalidConfigException
     */
    public function rotateStorageEncryptionKeys($newKeyName = null)
    {
        $cryptographer = $this->getCryptographer();

        $result = [];
        foreach (static::ENCRYPTED_MODELS as $modelInterface) {
            $modelClass = DiHelper::getValidatedClassName($modelInterface);
            if (!is_a($modelClass, Oauth2EncryptedStorageInterface::class, true)) {
                throw new InvalidConfigException($modelInterface . ' must implement '
                    . Oauth2EncryptedStorageInterface::class);
            }
            $result[$modelClass] = $modelClass::rotateStorageEncryptionKeys($cryptographer, $newKeyName);
        }

        return $result;
    }

    /**
     * Checks if the connection is using TLS or if the remote IP address is allowed to connect without TLS.
     * @return bool
     */
    public function validateTlsConnection()
    {
        if (Yii::$app->request->getIsSecureConnection()) {
            return true;
        }

        if (
            !empty($this->nonTlsAllowedRanges)
            && (new IpValidator(['ranges' => $this->nonTlsAllowedRanges]))->validate(Yii::$app->request->getRemoteIP())
        ) {
            return true;
        }

        return false;
    }

    /**
     * @return array
     * @throws InvalidConfigException
     */
    public function getStorageEncryptionKeyUsage()
    {
        $cryptographer = $this->getCryptographer();

        $result = [];
        foreach (static::ENCRYPTED_MODELS as $modelInterface) {
            $modelClass = DiHelper::getValidatedClassName($modelInterface);
            if (!is_a($modelClass, Oauth2EncryptedStorageInterface::class, true)) {
                throw new InvalidConfigException($modelInterface . ' must implement '
                    . Oauth2EncryptedStorageInterface::class);
            }

            $result[$modelClass] = $modelClass::getUsedStorageEncryptionKeys($cryptographer);
        }

        return $result;
    }

    /**
     * Generates a redirect Response to the client authorization page where the user is prompted to authorize the
     * client and requested scope.
     * @param Oauth2ClientAuthorizationRequestInterface $clientAuthorizationRequest
     * @return Response
     * @since 1.0.0
     */
    public function generateClientAuthReqRedirectResponse($clientAuthorizationRequest)
    {
        $this->setClientAuthReqSession($clientAuthorizationRequest);
        if (!empty($this->clientAuthorizationUrl)) {
            $url = $this->clientAuthorizationUrl;
        } else {
            $url = $this->uniqueId
                . '/' . Oauth2ConsentControllerInterface::CONTROLLER_NAME
                . '/' . Oauth2ConsentControllerInterface::ACTION_NAME_AUTHORIZE_CLIENT;
        }
        return Yii::$app->response->redirect([
            $url,
            'clientAuthorizationRequestId' => $clientAuthorizationRequest->getRequestId(),
        ]);
    }

    /**
     * Get a previously stored Client Authorization Request from the session.
     * @param string $requestId
     * @return Oauth2ClientAuthorizationRequestInterface|null
     * @since 1.0.0
     */
    public function getClientAuthReqSession($requestId)
    {
        if (empty($requestId)) {
            return null;
        }
        $key = static::CLIENT_AUTHORIZATION_REQUEST_SESSION_PREFIX . $requestId;
        $clientAuthorizationRequest = Yii::$app->session->get($key);
        if (!($clientAuthorizationRequest instanceof Oauth2ClientAuthorizationRequestInterface)) {
            if (!empty($clientAuthorizationRequest)) {
                Yii::warning(
                    'Found a ClientAuthorizationRequestSession with key "' . $key
                        . '", but it\'s not a ' . Oauth2ClientAuthorizationRequestInterface::class
                );
            }
            return null;
        }
        if ($clientAuthorizationRequest->getRequestId() !== $requestId) {
            Yii::warning(
                'Found a ClientAuthorizationRequestSession with key "' . $key
                    . '", but its request id does not match "' . $requestId . '".'
            );
            return null;
        }
        $clientAuthorizationRequest->setModule($this);

        return $clientAuthorizationRequest;
    }

    /**
     * Stores the Client Authorization Request in the session.
     * @param Oauth2ClientAuthorizationRequestInterface $clientAuthorizationRequest
     * @since 1.0.0
     */
    public function setClientAuthReqSession($clientAuthorizationRequest)
    {
        $requestId = $clientAuthorizationRequest->getRequestId();
        if (empty($requestId)) {
            throw new InvalidArgumentException('$scopeAuthorization must return a request id.');
        }
        $key = static::CLIENT_AUTHORIZATION_REQUEST_SESSION_PREFIX . $requestId;
        Yii::$app->session->set($key, $clientAuthorizationRequest);
    }

    /**
     * Stores whether the user was authenticated during the completion of the Client Authorization Request.
     * @param string $clientAuthorizationRequestId
     * @param bool $authenticatedDuringRequest
     * @since 1.0.0
     */
    public function setUserAuthenticatedDuringClientAuthRequest(
        $clientAuthorizationRequestId,
        $authenticatedDuringRequest
    ) {
        $clientAuthorizationRequest = $this->getClientAuthReqSession($clientAuthorizationRequestId);
        if ($clientAuthorizationRequest) {
            $clientAuthorizationRequest->setUserAuthenticatedDuringRequest($authenticatedDuringRequest);
            $this->setClientAuthReqSession($clientAuthorizationRequest);
        }
    }

    /**
     * Stores the user identity selected during the completion of the Client Authorization Request.
     * @param string $clientAuthorizationRequestId
     * @param Oauth2UserInterface $userIdentity
     * @since 1.0.0
     */
    public function setClientAuthRequestUserIdentity($clientAuthorizationRequestId, $userIdentity)
    {
        $clientAuthorizationRequest = $this->getClientAuthReqSession($clientAuthorizationRequestId);
        if ($clientAuthorizationRequest) {
            $clientAuthorizationRequest->setUserIdentity($userIdentity);
            $this->setClientAuthReqSession($clientAuthorizationRequest);
        }
    }

    /**
     * Clears a Client Authorization Request from the session storage.
     * @param string $requestId
     * @since 1.0.0
     */
    public function removeClientAuthReqSession($requestId)
    {
        if (empty($requestId)) {
            throw new InvalidArgumentException('$requestId can not be empty.');
        }
        $key = static::CLIENT_AUTHORIZATION_REQUEST_SESSION_PREFIX . $requestId;
        Yii::$app->session->remove($key);
    }

    /**
     * Generates a redirect Response when the Client Authorization Request is completed.
     * @param Oauth2ClientAuthorizationRequestInterface $clientAuthorizationRequest
     * @return Response
     * @since 1.0.0
     */
    public function generateClientAuthReqCompledRedirectResponse($clientAuthorizationRequest)
    {
        $clientAuthorizationRequest->processAuthorization();
        $this->setClientAuthReqSession($clientAuthorizationRequest);
        return Yii::$app->response->redirect($clientAuthorizationRequest->getAuthorizationRequestUrl());
    }

    /**
     * @return IdentityInterface|Oauth2UserInterface|Oauth2OidcUserInterface|null
     * @throws InvalidConfigException
     * @since 1.0.0
     */
    public function getUserIdentity()
    {
        $user = Yii::$app->user->identity;
        if (!empty($user) && !($user instanceof Oauth2UserInterface)) {
            throw new InvalidConfigException(
                'Yii::$app->user->identity (currently ' . get_class($user)
                    . ') must implement ' . Oauth2UserInterface::class
            );
        }
        return $user;
    }

    /**
     * Validates a bearer token authenticated request. Note: this method does not return a result but will throw
     * an exception when the authentication fails.
     * @throws InvalidConfigException
     * @throws Oauth2ServerException
     * @since 1.0.0
     */
    public function validateAuthenticatedRequest()
    {
        $psr7Request = Psr7Helper::yiiToPsr7Request(Yii::$app->request);

        $psr7Request = $this->getResourceServer()->validateAuthenticatedRequest($psr7Request);

        $this->_oauthClaims = $psr7Request->getAttributes();
        $this->_oauthClaimsAuthorizationHeader = Yii::$app->request->getHeaders()->get('Authorization');
    }

    /**
     * Find a user identity bases on an access token.
     * Note: validateAuthenticatedRequest() must be called before this method is called.
     * @param string $token
     * @param string $type
     * @return Oauth2UserInterface|null
     * @throws InvalidConfigException
     * @throws Oauth2ServerException
     * @see validateAuthenticatedRequest()
     * @since 1.0.0
     */
    public function findIdentityByAccessToken($token, $type)
    {
        if (!is_a($type, Oauth2HttpBearerAuthInterface::class, true)) {
            throw new InvalidCallException($type . ' must implement ' . Oauth2HttpBearerAuthInterface::class);
        }

        if (
            !preg_match('/^Bearer\s+(.*?)$/', $this->_oauthClaimsAuthorizationHeader, $matches)
            || !Yii::$app->security->compareString($matches[1], $token)
        ) {
            throw new InvalidCallException(
                'validateAuthenticatedRequest() must be called before findIdentityByAccessToken().'
            );
        }

        $userId = $this->getRequestOauthUserId();
        if (empty($userId)) {
            return null;
        }

        return $this->identityClass::findIdentity($userId);
    }

    /**
     * Generate a "Personal Access Token" (PAT) which can be used as an alternative to using passwords
     * for authentication (e.g. when using an API or command line).
     *
     * Note: Personal Access Tokens are intended to access resources on behalf users themselves.
     *       To grant access to resources on behalf of an organization, or for long-lived integrations,
     *       you most likely want to define an Oauth2 Client with the "Client Credentials" grant
     *       (https://oauth.net/2/grant-types/client-credentials).
     *
     * @param string $clientIdentifier The Oauth2 client identifier for which the PAT should be generated.
     * @param int|string $userIdentifier The identifier (primary key) of the user for which the PAT should be generated.
     * @param Oauth2ScopeInterface[]|string[]|string|null $scope The Access Token scope.
     * @param string|true|null $clientSecret If the client is a "confidential" client the secret is required.
     *        If the boolean value `true` is passed, the client secret is automatically injected.
     * @return Oauth2AccessTokenData
     */
    public function generatePersonalAccessToken($clientIdentifier, $userIdentifier, $scope = null, $clientSecret = null)
    {
        if (is_array($scope)) {
            $scopeIdentifiers = [];
            foreach ($scope as $scopeItem) {
                if (is_string($scopeItem)) {
                    $scopeIdentifiers[] = $scopeItem;
                } elseif ($scopeItem instanceof Oauth2ScopeInterface) {
                    $scopeIdentifiers[] = $scopeItem->getIdentifier();
                } else {
                    throw new InvalidArgumentException('If $scope is an array its elements must be either'
                        . ' a string or an instance of ' . Oauth2ScopeInterface::class);
                }
            }
            $scope = implode(' ', $scopeIdentifiers);
        }

        if ($clientSecret === true) {
            /** @var Oauth2ClientInterface $client */
            $client = $this->getClientRepository()->findModelByIdentifier($clientIdentifier);
            if ($client && $client->isConfidential()) {
                $clientSecret = $client->getDecryptedSecret($this->getCryptographer());
            } else {
                $clientSecret = null;
            }
        }

        $request = (new Psr7ServerRequest('POST', ''))->withParsedBody([
            'grant_type' => static::GRANT_TYPE_IDENTIFIER_PERSONAL_ACCESS_TOKEN,
            'client_id' => $clientIdentifier,
            'client_secret' => $clientSecret,
            'user_id' => $userIdentifier,
            'scope' => $scope,
        ]);

        return new Oauth2AccessTokenData(Json::decode(
            $this->getAuthorizationServer()
                ->respondToAccessTokenRequest(
                    $request,
                    new Psr7Response()
                )
                ->getBody()
                ->__toString()
        ));
    }

    /**
     * @inheritDoc
     */
    protected function getRequestOauthClaim($attribute, $default = null)
    {
        if (empty($this->_oauthClaimsAuthorizationHeader)) {
            // User authorization was not processed by Oauth2Module.
            return $default;
        }
        if (Yii::$app->request->getHeaders()->get('Authorization') !== $this->_oauthClaimsAuthorizationHeader) {
            throw new InvalidCallException(
                'App Request Authorization header does not match the processed Oauth header.'
            );
        }
        return $this->_oauthClaims[$attribute] ?? $default;
    }

    /**
     * Helper function to ensure the required properties are configured for the module.
     * @param string[] $properties
     * @throws InvalidConfigException
     * @since 1.0.0
     */
    protected function ensureProperties($properties)
    {
        foreach ($properties as $property) {
            if (empty($this->$property)) {
                throw new InvalidConfigException(__CLASS__ . '::$' . $property . ' must be set.');
            }
        }
    }
}
