<?php

namespace Yii2Oauth2ServerTests\unit\components\server\grants;

use GuzzleHttp\Psr7\ServerRequest;
use League\OAuth2\Server\ResponseTypes\BearerTokenResponse;
use Psr\Http\Message\ServerRequestInterface;
use rhertogh\Yii2Oauth2Server\components\server\grants\Oauth2RefreshTokenGrant;
use rhertogh\Yii2Oauth2Server\interfaces\models\external\user\Oauth2OidcUserSessionStatusInterface;
use rhertogh\Yii2Oauth2Server\models\Oauth2Client;
use rhertogh\Yii2Oauth2Server\Oauth2Module;
use Yii2Oauth2ServerTests\_helpers\TestUserModel;
use Yii2Oauth2ServerTests\_helpers\TestUserModelOidc;
use Yii2Oauth2ServerTests\unit\components\server\grants\_base\BaseOauth2GrantTest;

/**
 * @covers \rhertogh\Yii2Oauth2Server\components\server\grants\Oauth2RefreshTokenGrant
 */
class Oauth2RefreshTokenGrantTest extends BaseOauth2GrantTest
{
    protected function getMockGrant($module)
    {
        return new class ($module->getRefreshTokenRepository()) extends Oauth2RefreshTokenGrant {
            public static $scopes;
            public static $userId;

            protected function validateClient(ServerRequestInterface $request)
            {
                return new Oauth2Client([
                    'id' => 1003000,
                    //'identifier' => 'test-client-type-auth-code-valid',
                ]);
            }

            protected function validateOldRefreshToken(ServerRequestInterface $request, $clientId)
            {
                return [
                    'refresh_token_id' => 1004000,
                    'access_token_id' => 1001000,
                    'client_id' => 1003000,
                    'user_id' => static::$userId,
                    'scopes' => static::$scopes,
                ];
            }
        };
    }

    /**
     * @dataProvider respondToAccessTokenRequestProvider
     */
    public function testRespondToAccessTokenRequest(
        $moduleConfig,
        $scopes,
        $userId,
        $userHasActiveSession,
        $expectTokenResponse,
        $expectExceptionMessage
    ) {
        $this->mockWebApplication([
            'modules' => [
                'oauth2' => $moduleConfig,
            ],
        ]);

        $module = Oauth2Module::getInstance();

        TestUserModelOidc::$hasActiveSession = $userHasActiveSession;

        $refreshTokenGrant = $this->getMockGrant($module);

        $refreshTokenGrant::$scopes = $scopes;
        $refreshTokenGrant::$userId = $userId;
        $refreshTokenGrant->module = $module;
        $refreshTokenGrant->setScopeRepository($module->getScopeRepository());
        $refreshTokenGrant->setAccessTokenRepository($module->getAccessTokenRepository());
        $refreshTokenGrant->setPrivateKey($module->getPrivateKey());

        $request = new ServerRequest('POST', 'http://localhost');
        $responseType = new BearerTokenResponse();

        if ($expectExceptionMessage) {
            $this->expectExceptionMessage($expectExceptionMessage);
        }
        $refreshToken = $this->callInaccessibleMethod($refreshTokenGrant, 'respondToAccessTokenRequest', [
            $request,
            $responseType,
            new \DateInterval('PT1M')
        ]);

        if ($expectTokenResponse) {
            $this->assertInstanceOf(get_class($responseType), $refreshToken);
        }
    }

    /**
     * @see testRespondToAccessTokenRequest();
     */
    public function respondToAccessTokenRequestProvider()
    {
        return [
            [// OpenId Connect disabled.
                'moduleConfig' => [
                    'identityClass' => TestUserModelOidc::class,
                    'enableOpenIdConnect' => false,
                    'openIdConnectIssueRefreshTokenWithoutOfflineAccessScope' => false,
                ],
                'scopes' => ['openid'],
                'userId' => 123,
                'userHasActiveSession' => false,
                'expectTokenResponse' => true,
                'expectExceptionMessage' => null,
            ],
            [// OpenId Connect enabled, Refresh Token Without Offline Access disabled.
                'moduleConfig' => [
                    'identityClass' => TestUserModelOidc::class,
                    'enableOpenIdConnect' => true,
                    'openIdConnectIssueRefreshTokenWithoutOfflineAccessScope' => false,
                ],
                'scopes' => ['openid'],
                'userId' => 123,
                'userHasActiveSession' => false,
                'expectTokenResponse' => false,
                'expectExceptionMessage' => 'The resource owner or authorization server denied the request.',
            ],
            [// OpenId Connect enabled (but scope is not present), Refresh Token Without Offline Access disabled.
                'moduleConfig' => [
                    'enableOpenIdConnect' => true,
                    'openIdConnectIssueRefreshTokenWithoutOfflineAccessScope' => false,
                ],
                'scopes' => [],
                'userId' => 123,
                'userHasActiveSession' => false,
                'expectRefreshToken' => true,
                'expectExceptionMessage' => null,
            ],
            [// OpenId Connect enabled, Refresh Token Without Offline Access enabled, user offline.
                'moduleConfig' => [
                    'identityClass' => TestUserModelOidc::class,
                    'enableOpenIdConnect' => true,
                    'openIdConnectIssueRefreshTokenWithoutOfflineAccessScope' => true,
                ],
                'scopes' => ['openid'],
                'userId' => 123,
                'userHasActiveSession' => false,
                'expectTokenResponse' => false,
                'expectExceptionMessage' => 'The resource owner or authorization server denied the request.',
            ],
            [// OpenId Connect enabled, Refresh Token Without Offline Access enabled, user online.
                'moduleConfig' => [
                    'identityClass' => TestUserModelOidc::class,
                    'enableOpenIdConnect' => true,
                    'openIdConnectIssueRefreshTokenWithoutOfflineAccessScope' => true,
                ],
                'scopes' => ['openid'],
                'userId' => 123,
                'userHasActiveSession' => true,
                'expectTokenResponse' => true,
                'expectExceptionMessage' => null,
            ],
            [// OpenId Connect enabled, Refresh Token Without Offline Access disabled.
                'moduleConfig' => [
                    'identityClass' => TestUserModelOidc::class,
                    'enableOpenIdConnect' => true,
                    'openIdConnectIssueRefreshTokenWithoutOfflineAccessScope' => false,
                ],
                'scopes' => ['openid', 'offline_access'],
                'userId' => 123,
                'userHasActiveSession' => false,
                'expectTokenResponse' => true,
                'expectExceptionMessage' => null,
            ],
            [// invalid user id.
                'moduleConfig' => [
                    'identityClass' => TestUserModel::class,
                    'enableOpenIdConnect' => true,
                    'openIdConnectIssueRefreshTokenWithoutOfflineAccessScope' => true,
                ],
                'scopes' => ['openid'],
                'userId' => 99999,
                'userHasActiveSession' => true,
                'expectTokenResponse' => false,
                'expectExceptionMessage' => 'Unable to find user with id "99999".',
            ],
            [// invalid user class.
                'moduleConfig' => [
                    'identityClass' => TestUserModel::class,
                    'enableOpenIdConnect' => true,
                    'openIdConnectIssueRefreshTokenWithoutOfflineAccessScope' => true,
                ],
                'scopes' => ['openid'],
                'userId' => 123,
                'userHasActiveSession' => true,
                'expectTokenResponse' => false,
                'expectExceptionMessage' =>
                    'In order to support OpenId Connect Refresh Tokens without offline_access scope '
                        . TestUserModel::class . ' must implement ' . Oauth2OidcUserSessionStatusInterface::class,
            ],
        ];
    }
}
