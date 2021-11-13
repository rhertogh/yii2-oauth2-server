<?php

namespace Yii2Oauth2ServerTests\unit\components\openidconnect\server;

use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Signer\Rsa\Sha256;
use League\OAuth2\Server\CryptKey;
use League\OAuth2\Server\Entities\AccessTokenEntityInterface;
use rhertogh\Yii2Oauth2Server\components\openidconnect\claims\Oauth2OidcClaim;
use rhertogh\Yii2Oauth2Server\components\openidconnect\scopes\Oauth2OidcScope;
use rhertogh\Yii2Oauth2Server\components\openidconnect\server\Oauth2OidcBearerTokenResponse;
use rhertogh\Yii2Oauth2Server\interfaces\components\openidconnect\scope\Oauth2OidcClaimInterface;
use rhertogh\Yii2Oauth2Server\interfaces\components\openidconnect\scope\Oauth2OidcScopeCollectionInterface;
use rhertogh\Yii2Oauth2Server\interfaces\components\openidconnect\server\Oauth2OidcBearerTokenResponseInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\Oauth2OidcUserInterface;
use rhertogh\Yii2Oauth2Server\models\Oauth2AccessToken;
use rhertogh\Yii2Oauth2Server\models\Oauth2Client;
use rhertogh\Yii2Oauth2Server\models\Oauth2Scope;
use rhertogh\Yii2Oauth2Server\Oauth2Module;
use Yii;
use yii\helpers\ArrayHelper;
use Yii2Oauth2ServerTests\_helpers\TestUserModel;
use Yii2Oauth2ServerTests\_helpers\TestUserModelOidc;
use Yii2Oauth2ServerTests\unit\DatabaseTestCase;

/**
 * @covers \rhertogh\Yii2Oauth2Server\components\openidconnect\server\Oauth2OidcBearerTokenResponse
 */
class Oauth2OidcBearerTokenResponseTest extends DatabaseTestCase
{
    protected function getMockOidcBearerTokenResponse()
    {
        return new class (Oauth2Module::getInstance()) extends Oauth2OidcBearerTokenResponse {
            public function pubGetExtraParams(AccessTokenEntityInterface $accessToken)
            {
                return $this->getExtraParams($accessToken);
            }
        };
    }

    public function testGetModule()
    {
        $response = $this->getMockOidcBearerTokenResponse();
        $this->assertInstanceOf(Oauth2Module::class, $response->getModule());
    }

    public function testGetExtraParams()
    {
        $mockUserClass = get_class(new class extends TestUserModelOidc {

            public static Oauth2OidcBearerTokenResponseTest $testCase;
            public static \DateTimeImmutable $latestAuthenticatedAt;

            public static function findIdentity($id)
            {
                $mockUser = static::$testCase->getMockBuilder(TestUserModelOidc::class)
                    ->onlyMethods([
                        'getIdentifier',
                        'getLatestAuthenticatedAt',
                    ])
                    ->addMethods(['getCustomClaim'])
                    ->getMock();

                $mockUser->expects(static::$testCase->once())
                    ->method('getIdentifier')
                    ->willReturn(123);

                $mockUser->expects(static::$testCase->once())
                    ->method('getLatestAuthenticatedAt')
                    ->willReturnCallback(fn() => static::$latestAuthenticatedAt);

                $mockUser->expects(static::$testCase->once())
                    ->method('getCustomClaim')
                    ->willReturn('custom-claim-value');

                return $mockUser;
            }
        });

        $mockUserClass::$testCase = $this;
        $mockUserClass::$latestAuthenticatedAt = (new \DateTimeImmutable())->setTimestamp(random_int(1, 1000));

        $this->mockWebApplication([
            'modules' => [
                'oauth2' => [
                    'identityClass' => $mockUserClass,
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
        $response = $this->getMockOidcBearerTokenResponse();
        $response->setPrivateKey($module->getPrivateKey());
        $accessToken = new Oauth2AccessToken([
            'userIdentifier' => 123,
            'client' => new Oauth2Client([
                'identifier' => 'test-client',
            ]),
            'scopes' => [
                new Oauth2Scope(['identifier' => 'openid']),
                new Oauth2Scope(['identifier' => 'profile']),
                new Oauth2Scope(['identifier' => 'email']),
                new Oauth2Scope(['identifier' => 'address']),
                new Oauth2Scope(['identifier' => 'custom-scope']),
            ],
            'expiryDateTime' => new \DateTimeImmutable('+1 hour'),
        ]);

        $nonce = Yii::$app->security->generateRandomString();
        Yii::$app->request->setBodyParams(ArrayHelper::merge(Yii::$app->request->getBodyParams(), [
            'nonce' => $nonce,
        ]));
        $issueTime = (new \DateTimeImmutable('@' . time())); // ensure no micro seconds

        $extraParams = $response->pubGetExtraParams($accessToken);

        $this->assertIsArray($extraParams);
        $this->assertArrayHasKey('id_token', $extraParams);

        $jwtConfiguration = Configuration::forSymmetricSigner(
            new Sha256(),
            InMemory::plainText('')
        );
        $idToken = $jwtConfiguration->parser()->parse($extraParams['id_token']);
        $claims = $idToken->claims()->all();
        $this->assertEquals(['test-client'], $claims['aud']);
        $this->assertEquals('http://localhost', $claims['iss']);
        $this->assertGreaterThanOrEqual($issueTime, $claims['iat']);
        $this->assertLessThanOrEqual($issueTime->modify('+1 second'), $claims['iat']);
        $this->assertEquals($accessToken->getExpiryDateTime(), $claims['exp']);
        $this->assertEquals('123', $claims['sub']);
        $this->assertEquals($mockUserClass::$latestAuthenticatedAt->getTimestamp(), $claims['auth_time']);
        $this->assertEquals($nonce, $claims['nonce']);

        // Requested
        $this->assertArrayHasKey('profile', $claims);
        $this->assertArrayHasKey('email', $claims);
        $this->assertArrayHasKey('address', $claims);
        $this->assertEquals('custom-claim-value', $claims['custom-claim']);
        // Not requested
        $this->assertArrayNotHasKey('phone_number', $claims);
        $this->assertArrayNotHasKey('phone_number_verified', $claims);
    }

    public function testGetExtraParamsNoOpenIdScope()
    {
        $response = $this->getMockOidcBearerTokenResponse();
        $accessToken = new Oauth2AccessToken([
            'scopes' => [
                new Oauth2Scope([
                    'identifier' => 'profile',
                ]),
            ],
        ]);

        $extraParams = $response->pubGetExtraParams($accessToken);
        $this->assertEquals([], $extraParams);
    }

    public function testGetExtraParamsNonExistingUserIdentifier()
    {
        $response = $this->getMockOidcBearerTokenResponse();
        $accessToken = new Oauth2AccessToken([
            'userIdentifier' => 999,
            'scopes' => [
                new Oauth2Scope([
                    'identifier' => 'openid',
                ]),
            ],
        ]);

        $this->expectExceptionMessage('No user with identifier "999" found.');
        $response->pubGetExtraParams($accessToken);
    }
}
