<?php

namespace Yii2Oauth2ServerTests\unit\components\server\grants;

use rhertogh\Yii2Oauth2Server\components\factories\grants\Oauth2AuthCodeGrantFactory;
use rhertogh\Yii2Oauth2Server\interfaces\models\Oauth2RefreshTokenInterface;
use rhertogh\Yii2Oauth2Server\models\Oauth2AccessToken;
use rhertogh\Yii2Oauth2Server\models\Oauth2Scope;
use rhertogh\Yii2Oauth2Server\Oauth2Module;
use Yii2Oauth2ServerTests\unit\DatabaseTestCase;

/**
 * @covers \rhertogh\Yii2Oauth2Server\components\server\grants\Oauth2AuthCodeGrant
 */
class Oauth2AuthCodeGrantTest extends DatabaseTestCase
{
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

        $module = Oauth2Module::getInstance();
        $authCodeGrant = (new Oauth2AuthCodeGrantFactory(['module' => $module]))->getGrantType();
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
            [// OpenId Connect disabled, openid scope enabled
                'moduleConfig' => [
                    'enableOpenIdConnect' => false,
                    'openIdConnectIssueRefreshTokenWithoutOfflineAccessScope' => false,
                ],
                'scopes' => ['openid'],
                'expectRefreshToken' => true,
            ],
            [// OpenId Connect enabled, Refresh Token Without Offline Access disabled
                'moduleConfig' => [
                    'enableOpenIdConnect' => true,
                    'openIdConnectIssueRefreshTokenWithoutOfflineAccessScope' => false,
                ],
                'scopes' => ['openid'],
                'expectRefreshToken' => false,
            ],
            [// OpenId Connect enabled (but scope is not present), Refresh Token Without Offline Access disabled
                'moduleConfig' => [
                    'enableOpenIdConnect' => true,
                    'openIdConnectIssueRefreshTokenWithoutOfflineAccessScope' => false,
                ],
                'scopes' => [],
                'expectRefreshToken' => true,
            ],
            [// OpenId Connect enabled, Refresh Token Without Offline Access enabled
                'moduleConfig' => [
                    'enableOpenIdConnect' => true,
                    'openIdConnectIssueRefreshTokenWithoutOfflineAccessScope' => true,
                ],
                'scopes' => ['openid'],
                'expectRefreshToken' => true,
            ],
            [// OpenId Connect enabled, Refresh Token Without Offline Access disabled,
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
