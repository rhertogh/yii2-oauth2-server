<?php

namespace Yii2Oauth2ServerTests\unit\components\server\grants;

use rhertogh\Yii2Oauth2Server\components\factories\grants\Oauth2AuthCodeGrantFactory;
use rhertogh\Yii2Oauth2Server\interfaces\models\Oauth2AuthCodeInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\Oauth2RefreshTokenInterface;
use rhertogh\Yii2Oauth2Server\models\Oauth2AccessToken;
use rhertogh\Yii2Oauth2Server\models\Oauth2Client;
use rhertogh\Yii2Oauth2Server\models\Oauth2Scope;
use rhertogh\Yii2Oauth2Server\Oauth2Module;
use Yii2Oauth2ServerTests\unit\components\server\grants\_base\BaseOauth2GrantTest;

/**
 * @covers \rhertogh\Yii2Oauth2Server\components\server\grants\Oauth2AuthCodeGrant
 * @covers \rhertogh\Yii2Oauth2Server\components\server\grants\traits\Oauth2GrantTrait
 */
class Oauth2AuthCodeGrantTest extends BaseOauth2GrantTest
{
    protected function getMockGrant($module)
    {
        return (new Oauth2AuthCodeGrantFactory(['module' => $module]))->getGrantType();
    }

    public function testIssueAuthCode()
    {
        $this->mockWebApplication([
            'modules' => [
                //'oauth2' => $moduleConfig,
            ],
        ]);

        $authCodeGrant = $this->getMockGrant(Oauth2Module::getInstance());
        $client = new Oauth2Client([
            'id' => 1003005,
        ]);

        $authCode = $this->callInaccessibleMethod($authCodeGrant, 'issueAuthCode', [
            new \DateInterval('PT1M'),
            $client,
            123,
            'http://localhost/redirect_uri/'
        ]);

        $this->assertInstanceOf(Oauth2AuthCodeInterface::class, $authCode);
    }

    /**
     * @dataProvider issueRefreshTokenProvider
     */
    public function testIssueRefreshToken($moduleConfig, $scopes, $expectRefreshToken)
    {
        $this->mockWebApplication([
            'modules' => [
                'oauth2' => $moduleConfig,
            ],
        ]);

        $authCodeGrant = $this->getMockGrant(Oauth2Module::getInstance());
        $accessToken = new Oauth2AccessToken();
        foreach ($scopes as $scope) {
            $accessToken->addScope(new Oauth2Scope(['identifier' => $scope]));
        }

        $refreshToken = $this->callInaccessibleMethod($authCodeGrant, 'issueRefreshToken', [$accessToken]);
        if ($expectRefreshToken) {
            $this->assertInstanceOf(Oauth2RefreshTokenInterface::class, $refreshToken);
        } else {
            $this->assertNull($refreshToken);
        }
    }

    /**
     * @see testIssueRefreshToken();
     */
    public function issueRefreshTokenProvider()
    {
        return [
            [// OpenId Connect disabled, openid scope enabled.
                'moduleConfig' => [
                    'enableOpenIdConnect' => false,
                    'openIdConnectIssueRefreshTokenWithoutOfflineAccessScope' => false,
                ],
                'scopes' => ['openid'],
                'expectRefreshToken' => true,
            ],
            [// OpenId Connect enabled, Refresh Token Without Offline Access disabled.
                'moduleConfig' => [
                    'enableOpenIdConnect' => true,
                    'openIdConnectIssueRefreshTokenWithoutOfflineAccessScope' => false,
                ],
                'scopes' => ['openid'],
                'expectRefreshToken' => false,
            ],
            [// OpenId Connect enabled (but scope is not present), Refresh Token Without Offline Access disabled.
                'moduleConfig' => [
                    'enableOpenIdConnect' => true,
                    'openIdConnectIssueRefreshTokenWithoutOfflineAccessScope' => false,
                ],
                'scopes' => [],
                'expectRefreshToken' => true,
            ],
            [// OpenId Connect enabled, Refresh Token Without Offline Access enabled.
                'moduleConfig' => [
                    'enableOpenIdConnect' => true,
                    'openIdConnectIssueRefreshTokenWithoutOfflineAccessScope' => true,
                ],
                'scopes' => ['openid'],
                'expectRefreshToken' => true,
            ],
            [// OpenId Connect enabled, Refresh Token Without Offline Access disabled.
                'moduleConfig' => [
                    'enableOpenIdConnect' => true,
                    'openIdConnectIssueRefreshTokenWithoutOfflineAccessScope' => false,
                ],
                'scopes' => ['openid', 'offline_access'],
                'expectRefreshToken' => true,
            ],
        ];
    }
}
