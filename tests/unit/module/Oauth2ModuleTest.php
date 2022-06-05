<?php

namespace Yii2Oauth2ServerTests\unit\module;

use Codeception\Util\HttpCode;
use Defuse\Crypto\Exception\EnvironmentIsBrokenException;
use League\OAuth2\Server\CryptKey;
use League\OAuth2\Server\Grant\AuthCodeGrant;
use League\OAuth2\Server\Grant\ClientCredentialsGrant;
use League\OAuth2\Server\Grant\ImplicitGrant;
use League\OAuth2\Server\Grant\PasswordGrant;
use League\OAuth2\Server\Grant\RefreshTokenGrant;
use rhertogh\Yii2Oauth2Server\components\authorization\Oauth2ClientAuthorizationRequest;
use rhertogh\Yii2Oauth2Server\components\factories\grants\Oauth2AuthCodeGrantFactory;
use rhertogh\Yii2Oauth2Server\components\openidconnect\scopes\Oauth2OidcScope;
use rhertogh\Yii2Oauth2Server\components\openidconnect\scopes\Oauth2OidcScopeCollection;
use rhertogh\Yii2Oauth2Server\components\server\Oauth2AuthorizationServer;
use rhertogh\Yii2Oauth2Server\components\server\Oauth2ResourceServer;
use rhertogh\Yii2Oauth2Server\filters\auth\Oauth2HttpBearerAuth;
use rhertogh\Yii2Oauth2Server\helpers\DiHelper;
use rhertogh\Yii2Oauth2Server\interfaces\components\authorization\Oauth2ClientAuthorizationRequestInterface;
use rhertogh\Yii2Oauth2Server\interfaces\components\encryption\Oauth2EncryptorInterface;
use rhertogh\Yii2Oauth2Server\interfaces\components\factories\encryption\Oauth2EncryptionKeyFactoryInterface;
use rhertogh\Yii2Oauth2Server\interfaces\components\openidconnect\scope\Oauth2OidcScopeCollectionInterface;
use rhertogh\Yii2Oauth2Server\interfaces\components\openidconnect\scope\Oauth2OidcScopeInterface;
use rhertogh\Yii2Oauth2Server\interfaces\components\openidconnect\server\Oauth2OidcBearerTokenResponseInterface;
use rhertogh\Yii2Oauth2Server\interfaces\components\repositories\Oauth2AccessTokenRepositoryInterface;
use rhertogh\Yii2Oauth2Server\interfaces\controllers\web\Oauth2ConsentControllerInterface;
use rhertogh\Yii2Oauth2Server\interfaces\filters\auth\Oauth2HttpBearerAuthInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\Oauth2ClientInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\external\user\Oauth2UserInterface;
use rhertogh\Yii2Oauth2Server\models\Oauth2Client;
use rhertogh\Yii2Oauth2Server\Oauth2Module;
use Yii;
use yii\filters\auth\HttpBearerAuth;
use yii\helpers\ReplaceArrayValue;
use yii\helpers\UnsetArrayValue;
use yii\web\GroupUrlRule;
use yii\web\IdentityInterface;
use Yii2Oauth2ServerTests\_helpers\TestUserModel;
use Yii2Oauth2ServerTests\unit\DatabaseTestCase;

/**
 * @covers \rhertogh\Yii2Oauth2Server\Oauth2Module
 */
class Oauth2ModuleTest extends DatabaseTestCase
{
    public function testInstantiateModule()
    {
        $this->mockConsoleApplication();
        $this->assertInstanceOf(Oauth2Module::class, Oauth2Module::getInstance());
    }

    /**
     * @depends testInstantiateModule
     */
    public function testInstantiatingWithoutIdentityClassDefinition()
    {
        $this->expectExceptionMessage('$identityClass must be set.');
        $this->mockConsoleApplication([
            'modules' => [
                'oauth2' => [
                    'identityClass' => new UnsetArrayValue(),
                ],
            ],
        ]);
    }

    /**
     * @depends testInstantiateModule
     */
    public function testInstantiatingWithInvalidOauth2UserInterfaceDefinition()
    {
        $this->expectExceptionMessage('stdClass must implement ' . Oauth2UserInterface::class);
        $this->mockConsoleApplication([
            'modules' => [
                'oauth2' => [
                    'identityClass' => \stdClass::class,
                ],
            ],
        ]);
    }

    /**
     * @param string $property
     *
     * @depends      testInstantiateModule
     * @dataProvider instantiatingWithMissingConfigurationProvider
     */
    public function testInstantiatingWithMissingConfiguration($property, $serverRole)
    {
        $this->mockConsoleApplication([
            'modules' => [
                'oauth2' => [
                    $property => new UnsetArrayValue(),
                ],
            ],
        ]);

        $this->expectExceptionMessage('$' . $property . ' must be set.');
        if ($serverRole & Oauth2Module::SERVER_ROLE_AUTHORIZATION_SERVER) {
            Oauth2Module::getInstance()->getAuthorizationServer();
        } elseif ($serverRole & Oauth2Module::SERVER_ROLE_RESOURCE_SERVER) {
            Oauth2Module::getInstance()->getResourceServer();
        } else {
            throw new \InvalidArgumentException('Unkown server role: ' . $serverRole);
        }
    }

    /**
     * @return string[][]
     * @see testInstantiatingWithMissingConfiguration()
     */
    public function instantiatingWithMissingConfigurationProvider()
    {
        $reflectionClass = new \ReflectionClass(Oauth2Module::class);
        return array_merge(
            array_map(
                fn($property) => [$property, Oauth2Module::SERVER_ROLE_AUTHORIZATION_SERVER],
                $reflectionClass->getConstant('REQUIRED_SETTINGS_AUTHORIZATION_SERVER')
            ),
            array_map(
                fn($property) => [$property, Oauth2Module::SERVER_ROLE_RESOURCE_SERVER],
                $reflectionClass->getConstant('REQUIRED_SETTINGS_RESOURCE_SERVER')
            ),
        );
    }

    public function testCreateClient()
    {
        $this->mockConsoleApplication();

        $client = Oauth2Module::getInstance()->createClient(
            'test',
            'test',
            Oauth2Module::GRANT_TYPE_AUTH_CODE,
            'https://localhost/test',
            Oauth2ClientInterface::TYPE_CONFIDENTIAL,
            'very_secret',
            'openid email',
        );

        $this->assertInstanceOf(Oauth2ClientInterface::class, $client);
        $this->assertEquals(
            ['openid', 'email'],
            array_map(
                fn($scope) => $scope->getIdentifier(),
                $client->getAllowedScopes(['openid', 'email', 'address'])
            )
        );
    }

    public function testCreateClientNonExistingScope()
    {
        $this->mockConsoleApplication();

        $this->expectExceptionMessage('No scope with identifier "does-not-exists" found.');
        $client = Oauth2Module::getInstance()->createClient(
            'test',
            'test',
            Oauth2Module::GRANT_TYPE_AUTH_CODE,
            'https://localhost/test',
            Oauth2ClientInterface::TYPE_CONFIDENTIAL,
            'very_secret',
            'does-not-exists',
        );
    }

    public function testCreateClientInvalidSecret()
    {
        $this->mockConsoleApplication();

        $this->expectExceptionMessage('Secret should be at least 10 characters.');
        Oauth2Module::getInstance()->createClient(
            'test',
            'test',
            Oauth2Module::GRANT_TYPE_AUTH_CODE,
            'https://localhost/test',
            Oauth2ClientInterface::TYPE_CONFIDENTIAL,
            'test',
        );
    }

    public function testCreateClientInvalidServerRole()
    {
        $this->mockConsoleApplication([
            'modules' => [
                'oauth2' => [
                    'serverRole' => Oauth2Module::SERVER_ROLE_RESOURCE_SERVER,
                ],
            ],
        ]);

        $this->expectExceptionMessage('Oauth2 server role does not include authorization server.');
        Oauth2Module::getInstance()->createClient(
            'test',
            'test',
            Oauth2Module::GRANT_TYPE_AUTH_CODE,
            'https://localhost/test',
            Oauth2ClientInterface::TYPE_CONFIDENTIAL,
            'test',
        );
    }

    /**
     * @param $key
     * @dataProvider getPrivateKeyProvider
     */
    public function testGetPrivateKey($key)
    {
        $this->mockConsoleApplication([
            'modules' => [
                'oauth2' => [
                    'privateKey' => $key,
                ],
            ],
        ]);

        $module = Oauth2Module::getInstance();
        $key = $module->getPrivateKey();
        $this->assertInstanceOf(CryptKey::class, $key);
    }

    /**
     * @see testGetPrivateKey()
     * @return array[]
     */
    public function getPrivateKeyProvider()
    {
        $keyAlias = '@Yii2Oauth2ServerTests/_config/keys/private.key';
        $keyFile = Yii::getAlias($keyAlias);
        return [
            [ // Alias path.
                $keyAlias,
            ],
            [ // Absolute path.
                $keyFile,
            ],
            [ // Absolute path with file URI scheme.
                'file://' . $keyFile,
            ],
            [ // Key content.
                file_get_contents($keyFile),
            ]
        ];
    }

    /**
     * @param $key
     * @dataProvider getPublicKeyProvider
     */
    public function testGetPublicKey($key)
    {
        $this->mockConsoleApplication([
            'modules' => [
                'oauth2' => [
                    'publicKey' => $key,
                ],
            ],
        ]);

        $module = Oauth2Module::getInstance();
        $key = $module->getPublicKey();
        $this->assertInstanceOf(CryptKey::class, $key);
    }

    /**
     * @see testGetPublicKey()
     * @return array[]
     */
    public function getPublicKeyProvider()
    {
        $keyAlias = '@Yii2Oauth2ServerTests/_config/keys/public.key';
        $keyFile = Yii::getAlias($keyAlias);
        return [
            [ // Alias path.
                $keyAlias,
            ],
            [ // Absolute path.
                $keyFile,
            ],
            [ // Absolute path with file URI scheme.
                'file://' . $keyFile,
            ],
            [ // Key content.
                file_get_contents($keyFile),
            ]
        ];
    }

    /**
     * @depends testInstantiateModule
     */
    public function testGetAuthorizationServerInvalidRole()
    {
        $this->mockConsoleApplication([
            'modules' => [
                'oauth2' => [
                    'serverRole' => Oauth2Module::SERVER_ROLE_RESOURCE_SERVER,
                ],
            ],
        ]);

        $this->expectExceptionMessage('Oauth2 server role does not include authorization server.');
        Oauth2Module::getInstance()->getAuthorizationServer();
    }

    /**
     * @depends testInstantiateModule
     */
    public function testGetResourceServerInvalidRole()
    {
        $this->mockConsoleApplication([
            'modules' => [
                'oauth2' => [
                    'serverRole' => Oauth2Module::SERVER_ROLE_AUTHORIZATION_SERVER,
                ],
            ],
        ]);

        $this->expectExceptionMessage('Oauth2 server role does not include resource server.');
        Oauth2Module::getInstance()->getResourceServer();
    }

    /**
     * @depends testInstantiateModule
     */
    public function testMissingDefaultStorageEncryptionKey()
    {
        $this->mockConsoleApplication([
            'modules' => [
                'oauth2' => [
                    'defaultStorageEncryptionKey' => 'does not exist',
                ],
            ],
        ]);

        $this->expectExceptionMessage('Key "does not exist" is not set in $storageEncryptionKeys');
        Oauth2Module::getInstance()->getAuthorizationServer();
    }

    /**
     * @param string $interface
     * @param string $implementation
     *
     * @depends      testInstantiateModule
     * @dataProvider defaultInterfaceImplementationsProvider
     */
    public function testDefaultInterfaceConfiguration($interface, $implementation)
    {
        $this->mockConsoleApplication();
        $this->assertTrue(Yii::$container->has($interface));
        $this->assertEquals(DiHelper::getValidatedClassName($interface), $implementation);
    }

    /**
     * @return array[]
     * @see testDefaultInterfaceConfiguration()
     */
    public function defaultInterfaceImplementationsProvider()
    {
        $reflectionClass = new \ReflectionClass(Oauth2Module::class);
        $defaultInterfaceImplementations = $reflectionClass->getConstant('DEFAULT_INTERFACE_IMPLEMENTATIONS');

        $defaultInterfaces = [];
        foreach ($defaultInterfaceImplementations as $interface => $implementation) {
            $defaultInterfaces[] = [$interface, $implementation];
        }

        return $defaultInterfaces;
    }

    /**
     * @depends testInstantiateModule
     */
    public function testGetEncryptor()
    {
        $this->mockConsoleApplication();
        $module = Oauth2Module::getInstance();
        $encryptor = $module->getEncryptor();
        $data = 'test';
        $this->assertInstanceOf(Oauth2EncryptorInterface::class, $encryptor);
        $this->assertEquals($data, $encryptor->decrypt($encryptor->encryp($data)));
    }

    public function testRotateStorageEncryptionKeys()
    {
        $this->mockConsoleApplication();
        $module = Oauth2Module::getInstance();

        $numClientsWithSecret = Oauth2Client::find()->andWhere(['NOT', ['secret' => null]])->count();

        $rotateResult = $module->rotateStorageEncryptionKeys();

        $this->assertEquals($numClientsWithSecret, $rotateResult[Oauth2Client::class]);
    }

    /**
     * @depends testInstantiateModule
     */
    public function testGetAuthorizationServer()
    {
        $this->mockConsoleApplication();
        $server = Oauth2Module::getInstance()->getAuthorizationServer();

        $this->assertInstanceOf(Oauth2AuthorizationServer::class, $server);
    }

    /**
     * @depends testGetAuthorizationServer
     */
    public function testGetAuthorizationServerInvalidCodesEncryptionKey()
    {
        $this->mockConsoleApplication([
            'modules' => [
                'oauth2' => [
                    'codesEncryptionKey' => 'malformed',
                ],
            ],
        ]);

        $this->expectExceptionMessage(
            '$codesEncryptionKey is malformed: Encoding::hexToBin() input is not a hex string.'
        );
        Oauth2Module::getInstance()->getAuthorizationServer();
    }

    /**
     * @depends testGetAuthorizationServer
     */
    public function testGetAuthorizationServerBrokenEnvironment()
    {
        $this->mockConsoleApplication([
            'container' => [
                'definitions' => [
                    // phpcs:ignore Generic.Files.LineLength.TooLong -- readability actually better on single line
                    Oauth2EncryptionKeyFactoryInterface::class => new class implements Oauth2EncryptionKeyFactoryInterface {
                        public function createFromAsciiSafeString($keyString, $doNotTrim = null)
                        {
                            throw new EnvironmentIsBrokenException('test message');
                        }
                    },
                ],
            ],
        ]);

        $this->expectExceptionMessage('Could not instantiate key "2021-01-01": test message');
        Oauth2Module::getInstance()->getAuthorizationServer();
    }

    /**
     * @depends testInstantiateModule
     */
    public function testGetAuthorizationServerOpenIdConnect()
    {
        $this->mockConsoleApplication();

        $server = Oauth2Module::getInstance()->getAuthorizationServer();

        /** @var Oauth2OidcBearerTokenResponseInterface|null $responseType */
        $responseType = $this->getInaccessibleProperty($server, 'responseType');
        $this->assertInstanceOf(Oauth2OidcBearerTokenResponseInterface::class, $responseType);
    }

    public function testGetOidcScopeCollectionDirectlyConfigured()
    {
        $oidcScopeCollection = new Oauth2OidcScopeCollection();
        $this->mockConsoleApplication([
            'modules' => [
                'oauth2' => [
                    'openIdConnectScopes' => $oidcScopeCollection,
                ],
            ],
        ]);

        $this->assertEquals($oidcScopeCollection, Oauth2Module::getInstance()->getOidcScopeCollection());
    }

    public function testGetOidcScopeCollectionCallable()
    {
        $oidcScope = new Oauth2OidcScope([
            'identifier' => 'test-scope',
        ]);
        $this->mockConsoleApplication([
            'modules' => [
                'oauth2' => [
                    'openIdConnectScopes' => function () use ($oidcScope) {
                        return new Oauth2OidcScopeCollection([
                            'oidcScopes' => [
                                $oidcScope,
                            ],
                        ]);
                    },
                ],
            ],
        ]);

        $oidcScopeCollection = Oauth2Module::getInstance()->getOidcScopeCollection();
        $this->assertEquals($oidcScope, $oidcScopeCollection->getOidcScope('test-scope'));
    }

    public function testGetOidcScopeCollectionArray()
    {
        $this->mockConsoleApplication([
            'modules' => [
                'oauth2' => [
                    'openIdConnectScopes' => [
                        Oauth2OidcScopeInterface::OPENID_CONNECT_SCOPE_ADDRESS,
                    ],
                ],
            ],
        ]);

        $oidcScopeCollection = Oauth2Module::getInstance()->getOidcScopeCollection();

        $this->assertInstanceOf(
            Oauth2OidcScopeInterface::class,
            $oidcScopeCollection->getOidcScope(Oauth2OidcScopeInterface::OPENID_CONNECT_SCOPE_ADDRESS)
        );
    }

    public function testGetOidcScopeCollectionString()
    {
        $this->mockConsoleApplication([
            'modules' => [
                'oauth2' => [
                    'openIdConnectScopes' => Oauth2OidcScopeInterface::OPENID_CONNECT_SCOPE_PHONE,
                ],
            ],
        ]);

        $oidcScopeCollection = Oauth2Module::getInstance()->getOidcScopeCollection();

        $this->assertInstanceOf(
            Oauth2OidcScopeInterface::class,
            $oidcScopeCollection->getOidcScope(Oauth2OidcScopeInterface::OPENID_CONNECT_SCOPE_PHONE)
        );
    }

    public function testGetOidcScopeCollectionInvalidConfig()
    {
        $this->mockConsoleApplication([
            'modules' => [
                'oauth2' => [
                    'openIdConnectScopes' => new \stdClass(),
                ],
            ],
        ]);

        $this->expectExceptionMessage(
            '$openIdConnectScopes must be a callable, array, string or ' . Oauth2OidcScopeCollectionInterface::class
        );
        Oauth2Module::getInstance()->getOidcScopeCollection();
    }

    /**
     * @depends testInstantiateModule
     */
    public function testGetResourceServer()
    {
        $this->mockConsoleApplication();
        $server = Oauth2Module::getInstance()->getResourceServer();

        $this->assertInstanceOf(Oauth2ResourceServer::class, $server);
    }

    /**
     * @depends testInstantiateModule
     */
    public function testGetResourceServerCustomAccessTokenRevocationValidation()
    {
        $this->mockConsoleApplication([
            'modules' => [
                'oauth2' => [
                    'resourceServerAccessTokenRevocationValidation' => false,
                ],
            ],
        ]);
        $server = Oauth2Module::getInstance()->getResourceServer();
        /** @var Oauth2AccessTokenRepositoryInterface $accessTokenRepository */
        $accessTokenRepository = $this->getInaccessibleProperty($server, 'accessTokenRepository');
        $this->assertFalse($accessTokenRepository->getRevocationValidation());
    }

    /**
     * @param $grantTypeIdentifier
     * @param $grantTypeClass
     *
     * @depends      testGetAuthorizationServer
     * @dataProvider enabledGrantTypesProvider
     */
    public function testEnabledGrantTypes($grantTypeIdentifier, $grantTypeClass)
    {
        $this->mockConsoleApplication();
        $server = Oauth2Module::getInstance()->getAuthorizationServer();
        $enabledGrantTypes = $this->getInaccessibleProperty($server, 'enabledGrantTypes');

        $this->assertInstanceOf($grantTypeClass, $enabledGrantTypes[$grantTypeIdentifier]);
    }

    /**
     * @return string[][]
     * @see testEnabledGrantTypes()
     */
    public function enabledGrantTypesProvider()
    {
        return [
            ['authorization_code', AuthCodeGrant::class],
            ['client_credentials', ClientCredentialsGrant::class],
            ['implicit',           ImplicitGrant::class],
            ['password',           PasswordGrant::class],
            ['refresh_token',      RefreshTokenGrant::class],
        ];
    }

    public function testUrlRulesPrefix()
    {
        $prefix = 'test-prefix';
        $this->mockWebApplication([
            'modules' => [
                'oauth2' => [
                    'urlRulesPrefix' => $prefix,
                ],
            ],
        ]);

        $found = false;
        foreach (Yii::$app->urlManager->rules as $rule) {
            if ($rule instanceof GroupUrlRule) {
                if ($rule->prefix == $prefix && $rule->routePrefix == 'oauth2') {
                    $found = true;
                    break;
                }
            }
        }

        $this->assertTrue($found);
    }

    public function testCallableGrantTypesConfig()
    {
        $this->mockConsoleApplication([
            'modules' => [
                'oauth2' => [
                    'grantTypes' => new ReplaceArrayValue(function (Oauth2AuthorizationServer $server) {
                        $server->enableGrantType(new ClientCredentialsGrant());
                    }),
                ],
            ],
        ]);
        $server = Oauth2Module::getInstance()->getAuthorizationServer();
        $enabledGrantTypes = $this->getInaccessibleProperty($server, 'enabledGrantTypes');
        $this->assertArrayHasKey('client_credentials', $enabledGrantTypes);
        $this->assertInstanceOf(ClientCredentialsGrant::class, $enabledGrantTypes['client_credentials']);
    }

    public function testGrantTypeInterfaceGrantTypesConfig()
    {
        // Just using the app to create our mock $grantType.
        $this->mockConsoleApplication();
        $factory = Yii::createObject([
            'class' => Oauth2AuthCodeGrantFactory::class,
            'module' => Oauth2Module::getInstance(),
        ]);
        $grantType = $factory->getGrantType();
        $this->destroyApplication();

        $this->mockConsoleApplication([
            'modules' => [
                'oauth2' => [
                    'grantTypes' => $grantType,
                ],
            ],
        ]);

        $server = Oauth2Module::getInstance()->getAuthorizationServer();
        $enabledGrantTypes = $this->getInaccessibleProperty($server, 'enabledGrantTypes');
        $this->assertArrayHasKey('authorization_code', $enabledGrantTypes);
        $this->assertInstanceOf(AuthCodeGrant::class, $enabledGrantTypes['authorization_code']);
    }

    public function testInvalidGrantTypesConfig()
    {
        $this->mockConsoleApplication([
            'modules' => [
                'oauth2' => [
                    'grantTypes' => 123,
                ],
            ],
        ]);

        $this->expectExceptionMessage('Unknown grantType "123".');
        Oauth2Module::getInstance()->getAuthorizationServer();
    }

    public function testGenerateClientAuthReqRedirectResponse()
    {
        $this->mockWebApplication();
        $module = Oauth2Module::getInstance();
        $clientAuthorizationRequest = new Oauth2ClientAuthorizationRequest();
        $response = $module->generateClientAuthReqRedirectResponse($clientAuthorizationRequest);

        $url = Yii::$app->request->getHostInfo()
            . '/' . $module->uniqueId
            . '/' . Oauth2ConsentControllerInterface::ACTION_NAME_AUTHORIZE_CLIENT
            . '?clientAuthorizationRequestId=' . $clientAuthorizationRequest->getRequestId();
        $this->assertEquals(HttpCode::FOUND, $response->getStatusCode());
        $this->assertEquals($url, $response->getHeaders()->get('location'));
    }

    public function testGenerateClientAuthReqRedirectResponseWithCustomclientAuthorizationUrl()
    {
        $clientAuthorizationUrl = '/custom/auth/action';
        $this->mockWebApplication([
            'modules' => [
                'oauth2' => [
                    'clientAuthorizationUrl' => $clientAuthorizationUrl,
                ],
            ],
        ]);
        $module = Oauth2Module::getInstance();
        $clientAuthorizationRequest = new Oauth2ClientAuthorizationRequest();
        $response = $module->generateClientAuthReqRedirectResponse($clientAuthorizationRequest);

        $url = Yii::$app->request->getHostInfo()
            . $clientAuthorizationUrl
            . '?clientAuthorizationRequestId=' . $clientAuthorizationRequest->getRequestId();
        $this->assertEquals(HttpCode::FOUND, $response->getStatusCode());
        $this->assertEquals($url, $response->getHeaders()->get('location'));
    }

    public function testGetClientAuthReqSession()
    {
        $this->mockWebApplication();
        $module = Oauth2Module::getInstance();
        $user = new TestUserModel(['id' => 999]);
        Yii::$app->user->setIdentity($user);
        $keyPrefix = $this->getInaccessibleConstant(Oauth2Module::class, 'CLIENT_AUTHORIZATION_REQUEST_SESSION_PREFIX');

        $requestId = 123;
        $this->assertNull($module->getClientAuthReqSession($requestId));
        $key = $keyPrefix . $requestId;
        Yii::$app->session->set($key, new \stdClass());
        $this->assertNull( // Expect `null` since it's not an instance of Oauth2ClientAuthorizationRequestInterface.
            $module->getClientAuthReqSession($requestId)
        );
        Yii::$app->session->set($key, new Oauth2ClientAuthorizationRequest());
        $this->assertNull($module->getClientAuthReqSession($requestId)); // Expect `null` since id doesn't match.

        $clientAuthorizationRequest = new Oauth2ClientAuthorizationRequest([
            'userIdentifier' => $user->getIdentifier(),
        ]);
        $requestId = $clientAuthorizationRequest->getRequestId();
        $key = $keyPrefix . $requestId;
        Yii::$app->session->set($key, $clientAuthorizationRequest);
        $this->assertEquals($clientAuthorizationRequest, $module->getClientAuthReqSession($requestId));
        $this->assertEquals($module, $module->getClientAuthReqSession($requestId)->getModule());
    }

    public function testSetClientAuthReqSession()
    {
        $this->mockWebApplication();
        $module = Oauth2Module::getInstance();
        $keyPrefix = $this->getInaccessibleConstant(Oauth2Module::class, 'CLIENT_AUTHORIZATION_REQUEST_SESSION_PREFIX');
        $clientAuthorizationRequest = new Oauth2ClientAuthorizationRequest();
        $requestId = $clientAuthorizationRequest->getRequestId();
        $key = $keyPrefix . $requestId;

        $module->setClientAuthReqSession($clientAuthorizationRequest);
        $this->assertEquals($clientAuthorizationRequest, Yii::$app->session->get($key));
    }

    public function testSetClientAuthReqSessionWithInvalidRequestId()
    {
        $this->mockWebApplication();
        $module = Oauth2Module::getInstance();
        $invalidClientAuthorizationRequest = new class extends Oauth2ClientAuthorizationRequest {
            public function getRequestId()
            {
                return null;
            }
        };

        $this->expectExceptionMessage('$scopeAuthorization must return a request id.');
        $module->setClientAuthReqSession($invalidClientAuthorizationRequest);
    }

    public function testRemoveClientAuthReqSession()
    {
        $this->mockWebApplication();
        $module = Oauth2Module::getInstance();
        $user = new TestUserModel(['id' => 999]);
        Yii::$app->user->setIdentity($user);
        $keyPrefix = $this->getInaccessibleConstant(Oauth2Module::class, 'CLIENT_AUTHORIZATION_REQUEST_SESSION_PREFIX');
        $clientAuthorizationRequest = new Oauth2ClientAuthorizationRequest([
            'userIdentifier' => $user->getIdentifier(),
        ]);
        $requestId = $clientAuthorizationRequest->getRequestId();
        $key = $keyPrefix . $requestId;

        Yii::$app->session->set($key, $clientAuthorizationRequest);
        $this->assertEquals($clientAuthorizationRequest, Yii::$app->session->get($key));
        $this->assertEquals($clientAuthorizationRequest, $module->getClientAuthReqSession($requestId));
        $module->removeClientAuthReqSession($requestId);
        $this->assertNull(Yii::$app->session->get($key));
        $this->assertNull($module->getClientAuthReqSession($requestId));
    }

    public function testRemoveClientAuthReqSessionEmptyId()
    {
        $this->mockWebApplication();
        $module = Oauth2Module::getInstance();

        $this->expectExceptionMessage('$requestId can not be empty.');
        $module->removeClientAuthReqSession('');
    }

    public function testGenerateClientAuthReqCompletedRedirectResponse()
    {
        $this->mockWebApplication();
        $module = Oauth2Module::getInstance();
        $keyPrefix = $this->getInaccessibleConstant(Oauth2Module::class, 'CLIENT_AUTHORIZATION_REQUEST_SESSION_PREFIX');
        $authorizeUrl = 'http://localhost/authorize_url';
        $clientAuthorizationRequest = new Oauth2ClientAuthorizationRequest([
            'module' => $module,
            'clientIdentifier' => 'test-client-type-auth-code-valid',
            'userIdentity' => TestUserModel::findOne(123),
            'requestedScopeIdentifiers' => ['user.id.read', 'user.username.read', 'user.email_address.read'],
            'grantType' => Oauth2Module::GRANT_TYPE_AUTH_CODE,
            'authorizeUrl' => $authorizeUrl,
            'redirectUri' => 'http://localhost/redirect_uri',
        ]);
        $clientAuthorizationRequest->setAuthorizationStatus(
            Oauth2ClientAuthorizationRequestInterface::AUTHORIZATION_APPROVED
        );
        $requestId = $clientAuthorizationRequest->getRequestId();
        $key = $keyPrefix . $requestId;

        $response = $module->generateClientAuthReqCompledRedirectResponse($clientAuthorizationRequest);

        /** @var Oauth2ClientAuthorizationRequest $sessionClientAuthorizationRequest */
        $sessionClientAuthorizationRequest = Yii::$app->session->get($key);
        $returnUrl = $authorizeUrl
            . '?clientAuthorizationRequestId=' . $clientAuthorizationRequest->getRequestId();

        $this->assertEquals($clientAuthorizationRequest, $sessionClientAuthorizationRequest);
        $this->assertTrue($sessionClientAuthorizationRequest->isCompleted());
        $this->assertEquals(HttpCode::FOUND, $response->getStatusCode());
        $this->assertEquals($returnUrl, $response->getHeaders()->get('location'));
    }

    public function testGetAppUser()
    {
        $this->mockWebApplication();
        $module = Oauth2Module::getInstance();
        $user = new TestUserModel(['id' => 999]);

        $this->assertNull($module->getUserIdentity());
        Yii::$app->user->setIdentity($user);
        $this->assertEquals($user, $module->getUserIdentity());
        Yii::$app->user->setIdentity(null);
        $this->assertNull($module->getUserIdentity());
    }

    public function testGetAppUserInvalidConfig()
    {
        $this->mockWebApplication();
        $module = Oauth2Module::getInstance();
        $user = new class implements IdentityInterface {
            public static function findIdentity($id)
            {
            }
            public static function findIdentityByAccessToken($token, $type = null)
            {
            }
            public function getId()
            {
            }
            public function getAuthKey()
            {
            }
            public function validateAuthKey($authKey)
            {
            }
        };

        Yii::$app->user->setIdentity($user);
        $this->expectExceptionMessage(
            'Yii::$app->user->identity (currently ' . get_class($user) . ') must implement '
                . Oauth2UserInterface::class
        );
        $module->getUserIdentity();
    }

    public function testValidateAuthenticatedRequest()
    {
        $this->mockWebApplication([
            'modules' => [
                'oauth2' => [
                    // Token revocation validation is tested during functional testing.
                    'resourceServerAccessTokenRevocationValidation' => false,
                ]
            ]
        ]);
        Yii::$app->request->headers->set('Authorization', 'Bearer ' . $this->validAccessToken);
        $module = Oauth2Module::getInstance();

        $module->validateAuthenticatedRequest();
        $this->assertEquals(123, $module->getRequestOauthUserId());
        $this->assertEquals(
            [
                'user.username.read',
                'user.email_address.read',
            ],
            $module->getRequestOauthScopeIdentifiers()
        );
    }

    /**
     * @depends testValidateAuthenticatedRequest
     */
    public function testFindIdentityByAccessToken()
    {
        $this->mockWebApplication([
            'modules' => [
                'oauth2' => [
                    // Token revocation validation is tested during functional testing.
                    'resourceServerAccessTokenRevocationValidation' => false,
                ]
            ]
        ]);
        Yii::$app->request->headers->set('Authorization', 'Bearer ' . $this->validAccessToken);
        $module = Oauth2Module::getInstance();

        $module->validateAuthenticatedRequest();
        $identity = $module->findIdentityByAccessToken($this->validAccessToken, Oauth2HttpBearerAuth::class);
        $this->assertEquals(123, $identity->getIdentifier());
    }

    /**
     * @depends testValidateAuthenticatedRequest
     */
    public function testFindIdentityByAccessTokenChangedToken()
    {
        $this->mockWebApplication([
            'modules' => [
                'oauth2' => [
                    // Token revocation validation is tested during functional testing.
                    'resourceServerAccessTokenRevocationValidation' => false,
                ]
            ]
        ]);
        Yii::$app->request->headers->set('Authorization', 'Bearer ' . $this->validAccessToken);
        $module = Oauth2Module::getInstance();
        $module->validateAuthenticatedRequest();

        $this->expectExceptionMessage(
            'validateAuthenticatedRequest() must be called before findIdentityByAccessToken().'
        );
        $module->findIdentityByAccessToken('other token', Oauth2HttpBearerAuth::class);
    }

    public function testFindIdentityByAccessTokenInvalidType()
    {
        $this->mockWebApplication();
        $module = Oauth2Module::getInstance();

        $this->expectExceptionMessage(
            'yii\filters\auth\HttpBearerAuth must implement ' . Oauth2HttpBearerAuthInterface::class
        );
        $module->findIdentityByAccessToken($this->validAccessToken, HttpBearerAuth::class);
    }

    public function testFindIdentityByAccessTokenNoUserId()
    {
        $this->mockWebApplication();
        $module = Oauth2Module::getInstance();
        Yii::$app->request->headers->set('Authorization', 'Bearer ' . $this->validAccessToken);
        $this->setInaccessibleProperty($module, '_oauthClaimsAuthorizationHeader', 'Bearer ' . $this->validAccessToken);

        $this->assertNull($module->findIdentityByAccessToken($this->validAccessToken, Oauth2HttpBearerAuth::class));
    }

    public function testGetOauthClaimWithoutProcessing()
    {
        $this->mockWebApplication();
        $module = Oauth2Module::getInstance();
        Yii::$app->request->headers->set('Authorization', 'Bearer ' . $this->validAccessToken);

        $expectedValue = 'defaultTest';
        $value = $this->callInaccessibleMethod($module, 'getRequestOauthClaim', ['claim', $expectedValue]);
        $this->assertEquals($expectedValue, $value);
    }

    public function testGetOauthClaimWithChangedAuthHeader()
    {
        $this->mockWebApplication();
        $module = Oauth2Module::getInstance();
        Yii::$app->request->headers->set('Authorization', 'Bearer ' . $this->validAccessToken);

        $this->setInaccessibleProperty($module, '_oauthClaimsAuthorizationHeader', 'nope');
        $this->expectExceptionMessage('App Request Authorization header does not match the processed Oauth header.');
        $module->getRequestOauthUserId();
    }
}
