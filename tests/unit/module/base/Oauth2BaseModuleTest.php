<?php

namespace Yii2Oauth2ServerTests\unit\module\base;

use League\OAuth2\Server\Oauth2AuthorizationServerInterface;
use rhertogh\Yii2Oauth2Server\base\Oauth2BaseModule;
use rhertogh\Yii2Oauth2Server\components\openidconnect\claims\Oauth2OidcClaim;
use rhertogh\Yii2Oauth2Server\components\openidconnect\scopes\Oauth2OidcScope;
use rhertogh\Yii2Oauth2Server\components\openidconnect\scopes\Oauth2OidcScopeCollection;
use rhertogh\Yii2Oauth2Server\interfaces\components\factories\grants\base\Oauth2GrantTypeFactoryInterface;
use rhertogh\Yii2Oauth2Server\interfaces\components\openidconnect\scope\Oauth2OidcClaimInterface;
use rhertogh\Yii2Oauth2Server\interfaces\components\openidconnect\scope\Oauth2OidcScopeCollectionInterface;
use rhertogh\Yii2Oauth2Server\interfaces\components\repositories\base\Oauth2RepositoryInterface;
use rhertogh\Yii2Oauth2Server\interfaces\components\repositories\Oauth2AccessTokenRepositoryInterface;
use rhertogh\Yii2Oauth2Server\interfaces\components\repositories\Oauth2AuthCodeRepositoryInterface;
use rhertogh\Yii2Oauth2Server\interfaces\components\repositories\Oauth2ClientRepositoryInterface;
use rhertogh\Yii2Oauth2Server\interfaces\components\repositories\Oauth2RefreshTokenRepositoryInterface;
use rhertogh\Yii2Oauth2Server\interfaces\components\repositories\Oauth2ScopeRepositoryInterface;
use rhertogh\Yii2Oauth2Server\interfaces\components\repositories\Oauth2UserRepositoryInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\external\user\Oauth2OidcUserInterface;
use rhertogh\Yii2Oauth2Server\Oauth2Module;
use Yii;
use Yii2Oauth2ServerTests\_helpers\TestUserModel;
use Yii2Oauth2ServerTests\_helpers\TestUserModelOidc;
use Yii2Oauth2ServerTests\unit\TestCase;

/**
 * @covers \rhertogh\Yii2Oauth2Server\base\Oauth2BaseModule
 */
class Oauth2BaseModuleTest extends TestCase
{
    public function testGetGrantTypeId()
    {
        // phpcs:disable Generic.Files.LineLength.TooLong -- readability acually better on single line
        $this->assertEquals(Oauth2Module::GRANT_TYPE_AUTH_CODE, Oauth2Module::getGrantTypeId('authorization_code'));
        $this->assertEquals(Oauth2Module::GRANT_TYPE_CLIENT_CREDENTIALS, Oauth2Module::getGrantTypeId('client_credentials'));
        $this->assertEquals(Oauth2Module::GRANT_TYPE_REFRESH_TOKEN, Oauth2Module::getGrantTypeId('refresh_token'));
        $this->assertEquals(Oauth2Module::GRANT_TYPE_IMPLICIT, Oauth2Module::getGrantTypeId('implicit'));
        $this->assertEquals(Oauth2Module::GRANT_TYPE_PASSWORD, Oauth2Module::getGrantTypeId('password'));

        $this->assertNull(Oauth2Module::getGrantTypeId('non-exiting-grant-type'));
        // phpcs:enable Generic.Files.LineLength.TooLong
    }

    public function testGetGrantTypeIdentifier()
    {
        // phpcs:disable Generic.Files.LineLength.TooLong -- readability acually better on single line
        $this->assertEquals('authorization_code', Oauth2Module::getGrantTypeIdentifier(Oauth2Module::GRANT_TYPE_AUTH_CODE));
        $this->assertEquals('client_credentials', Oauth2Module::getGrantTypeIdentifier(Oauth2Module::GRANT_TYPE_CLIENT_CREDENTIALS));
        $this->assertEquals('refresh_token', Oauth2Module::getGrantTypeIdentifier(Oauth2Module::GRANT_TYPE_REFRESH_TOKEN));
        $this->assertEquals('implicit', Oauth2Module::getGrantTypeIdentifier(Oauth2Module::GRANT_TYPE_IMPLICIT));
        $this->assertEquals('password', Oauth2Module::getGrantTypeIdentifier(Oauth2Module::GRANT_TYPE_PASSWORD));

        $this->assertNull(Oauth2Module::getGrantTypeIdentifier(999999999));
        // phpcs:enable Generic.Files.LineLength.TooLong
    }

    /**
     * @param Oauth2GrantTypeFactoryInterface|string $factoryInterface
     * @param string $identifier
     *
     * @dataProvider defaultGrantFactoryIdentifiersProvider
     */
    public function testDefaultGrantFactoryIdentifiers($factoryInterface, $identifier)
    {
        $this->mockConsoleApplication();

        /** @var Oauth2GrantTypeFactoryInterface $factory */
        $factory = Yii::createObject([
            'class' => $factoryInterface,
            'module' => Oauth2Module::getInstance(),
        ]);
        $grantType = $factory->getGrantType();

        $this->assertEquals($identifier, $grantType->getIdentifier());
    }

    /**
     * @return string[][]
     * @see testDefaultGrantFactoryIdentifiers()
     */
    public function defaultGrantFactoryIdentifiersProvider()
    {
        $defaultFactories = $this->getInaccessibleConstant(Oauth2Module::class, 'DEFAULT_GRANT_TYPE_FACTORIES');

        $output = [];
        foreach ($defaultFactories as $id => $defaultFactory) {
            $output[] = [$defaultFactory, Oauth2Module::getGrantTypeIdentifier($id)];
        }
        return $output;
    }


    /**
     * @param string $repositoryName
     * @param Oauth2RepositoryInterface|string $repositoryInterface
     *
     * @dataProvider getRepositoryProvider
     */
    public function testGetRepository($repositoryName, $repositoryInterface)
    {
        $this->mockConsoleApplication();
        $module = Oauth2Module::getInstance();
        $repository = $module->{'get' . $repositoryName}();
        $this->assertInstanceOf($repositoryInterface, $repository);
    }

    /**
     * @return string[][]
     * @see testGetRepository()
     */
    public function getRepositoryProvider()
    {
        return [
            ['AccessTokenRepository', Oauth2AccessTokenRepositoryInterface::class],
            ['AuthCodeRepository', Oauth2AuthCodeRepositoryInterface::class],
            ['ClientRepository', Oauth2ClientRepositoryInterface::class],
            ['RefreshTokenRepository', Oauth2RefreshTokenRepositoryInterface::class],
            ['ScopeRepository', Oauth2ScopeRepositoryInterface::class],
            ['UserRepository', Oauth2UserRepositoryInterface::class],
        ];
    }

    /**
     * @param string $getterName
     * @param mixed $expected
     *
     * @dataProvider oauthClaimGettersProvider
     */
    public function testRequestOauthClaimGetters($getterName, $expected)
    {
        $baseModule = new class ('testOauth2BaseModule') extends Oauth2BaseModule {
            public function getOidcScopeCollection()
            {
            }

            protected function getRequestOauthClaim($attribute, $default = null)
            {
                $claims = [
                    'oauth_access_token_id' => 'oauth_access_token_id_test',
                    'oauth_client_id' => 'oauth_client_id_test',
                    'oauth_user_id' => 'oauth_user_id_test',
                    'oauth_scopes' => ['oauth_scopes_test1', 'oauth_scopes_test2'],
                ];

                return $claims[$attribute] ?? $default;
            }
        };

        $this->assertEquals($expected, $baseModule->{$getterName}());
    }


    /**
     * @return string[][]
     * @see testRequestOauthClaimGetters()
     */
    public function oauthClaimGettersProvider()
    {
        return [
            ['getRequestOauthAccessTokenIdentifier', 'oauth_access_token_id_test'],
            ['getRequestOauthClientIdentifier', 'oauth_client_id_test'],
            ['getRequestOauthUserId', 'oauth_user_id_test'],
            ['getRequestOauthScopeIdentifiers', ['oauth_scopes_test1', 'oauth_scopes_test2']],
        ];
    }

    public function testRequestHasScope()
    {
        $baseModule = new class ('testOauth2BaseModule') extends Oauth2BaseModule {
            public $claims;

            public function getOidcScopeCollection()
            {
            }

            protected function getRequestOauthClaim($attribute, $default = null)
            {
                return $this->claims[$attribute] ?? $default;
            }
        };

        $this->assertFalse($baseModule->requestHasScope('oauth_scopes_test1'));

        $baseModule->claims = [
            'oauth_access_token_id' => 'oauth_access_token_id_test',
            'oauth_scopes' => ['oauth_scopes_test1', 'oauth_scopes_test2'],
        ];

        $this->assertTrue($baseModule->requestHasScope('oauth_scopes_test1', true));
        $this->assertTrue($baseModule->requestHasScope('oauth_scopes_test1', false));
        $this->assertTrue($baseModule->requestHasScope('oauth_scopes_test2', true));
        $this->assertTrue($baseModule->requestHasScope('oauth_scopes_test2', false));
        $this->assertFalse($baseModule->requestHasScope('oauth_scopes_test3', true));
        $this->assertFalse($baseModule->requestHasScope('oauth_scopes_test3', false));

        $baseModule->claims = null;

        $this->assertFalse($baseModule->requestHasScope('oauth_scopes_test1', true));
        $this->assertTrue($baseModule->requestHasScope('oauth_scopes_test1', false));
    }

    public function testGetSetOpenIdConnectScopes()
    {
        $baseModule = new class ('testOauth2BaseModule') extends Oauth2BaseModule {
            public function getOidcScopeCollection()
            {
            }
            protected function getRequestOauthClaim($attribute, $default = null)
            {
            }
        };

        $this->assertEquals(
            Oauth2OidcScopeCollectionInterface::OPENID_CONNECT_DEFAULT_SCOPES,
            $baseModule->getOpenIdConnectScopes()
        );

        $scopeCollection = new Oauth2OidcScopeCollection();
        $this->setInaccessibleProperty($baseModule, '_oidcScopeCollection', $scopeCollection);

        $openIdConnectScopes = ['scope1', 'scope2'];
        $this->assertEquals($baseModule, $baseModule->setOpenIdConnectScopes($openIdConnectScopes));
        $this->assertEquals($openIdConnectScopes, $baseModule->getOpenIdConnectScopes());
        $this->assertNull($this->getInaccessibleProperty($baseModule, '_oidcScopeCollection'));
    }

    public function testGenerateOpenIdConnectUserClaimsToken()
    {
        $latestAuthenticatedAt = new \DateTimeImmutable();

        $mockUser = $this->getMockBuilder(TestUserModelOidc::class)
            ->onlyMethods([
                'getIdentifier',
                'getLatestAuthenticatedAt',
                'getOpenIdConnectClaimValue',
            ])
            ->addMethods(['getCustomClaim'])
            ->getMock();

        $mockUser->expects($this->once())
            ->method('getIdentifier')
            ->willReturn(123);

        $mockUser->expects($this->once())
            ->method('getLatestAuthenticatedAt')
            ->willReturnCallback(fn() => $latestAuthenticatedAt);

        $mockUser->expects($this->exactly(18))
            ->method('getOpenIdConnectClaimValue')
            ->with(
                $this->callback(function ($claim) {
                    return $claim instanceof Oauth2OidcClaimInterface;
                }),
                $this->callback(function ($module) {
                    return $module === Oauth2Module::getInstance();
                }),
            )
            ->willReturnCallback(function (...$parameters) use ($mockUser) {
                $reflectionMethod = new \ReflectionMethod(TestUserModelOidc::class, 'getOpenIdConnectClaimValue');
                return $reflectionMethod->invoke($mockUser, ...$parameters);
            });

        $mockUser->expects($this->once())
            ->method('getCustomClaim')
            ->willReturn('custom-claim-value');

        $this->mockWebApplication([
            'modules' => [
                'oauth2' => [
                    'openIdConnectScopes' => [
                        ...Oauth2OidcScopeCollectionInterface::OPENID_CONNECT_DEFAULT_SCOPES,
                        new Oauth2OidcScope([
                            'identifier' => 'custom-scope',
                            'claims' => [
                                new Oauth2OidcClaim([
                                    'identifier' => 'custom-claim',
                                    'determiner' => 'getCustomClaim',
                                ]),
                            ],
                        ]),
                    ],
                ],
            ],
        ]);

        $module = Oauth2Module::getInstance();
        $scopeIdentifiers = ['openid', 'profile', 'email', 'address', 'custom-scope'];
        $nonce = Yii::$app->security->generateRandomString();
        $expiryDateTime = new \DateTimeImmutable('+1 hour');

        $issueTime = (new \DateTimeImmutable('@' . time())); // ensure no micro seconds.

        $idToken = $this->callInaccessibleMethod($module, 'generateOpenIdConnectUserClaimsToken', [
            $mockUser,
            'test-client',
            $module->getPrivateKey(),
            $scopeIdentifiers,
            $nonce,
            $expiryDateTime,
        ]);

        $claims = $idToken->claims()->all();
        $this->assertEquals(['test-client'], $claims['aud']);
        $this->assertEquals('http://localhost', $claims['iss']);
        $this->assertGreaterThanOrEqual($issueTime, $claims['iat']);
        $this->assertLessThanOrEqual($issueTime->modify('+1 second'), $claims['iat']);
        $this->assertEquals($expiryDateTime, $claims['exp']);
        $this->assertEquals('123', $claims['sub']);
        $this->assertEquals($latestAuthenticatedAt->getTimestamp(), $claims['auth_time']);
        $this->assertEquals($nonce, $claims['nonce']);

        // Requested.
        $this->assertArrayHasKey('profile', $claims);
        $this->assertArrayHasKey('email', $claims);
        $this->assertArrayHasKey('address', $claims);
        $this->assertEquals('custom-claim-value', $claims['custom-claim']);
        // Not requested.
        $this->assertArrayNotHasKey('phone_number', $claims);
        $this->assertArrayNotHasKey('phone_number_verified', $claims);
    }

    public function testGenerateOpenIdConnectUserClaimsTokenInvalidUser()
    {
        $this->mockWebApplication();

        $module = Oauth2Module::getInstance();
        $user = new TestUserModel();
        $scopeIdentifiers = ['openid', 'profile', 'email', 'address', 'custom-scope'];

        $this->expectExceptionMessage(
            'In order to support OpenID Connect ' . get_class($user) . ' must implement '
            . Oauth2OidcUserInterface::class
        );
        $this->callInaccessibleMethod($module, 'generateOpenIdConnectUserClaimsToken', [
            $user,
            'test-client',
            $module->getPrivateKey(),
            $scopeIdentifiers,
        ]);
    }
}
