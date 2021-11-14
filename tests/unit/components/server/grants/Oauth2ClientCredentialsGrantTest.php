<?php

namespace Yii2Oauth2ServerTests\unit\components\server\grants;

use rhertogh\Yii2Oauth2Server\components\factories\grants\Oauth2ClientCredentialsGrantFactory;
use rhertogh\Yii2Oauth2Server\interfaces\models\Oauth2AccessTokenInterface;
use rhertogh\Yii2Oauth2Server\models\Oauth2Client;
use rhertogh\Yii2Oauth2Server\models\Oauth2Scope;
use rhertogh\Yii2Oauth2Server\Oauth2Module;
use Yii2Oauth2ServerTests\unit\DatabaseTestCase;

/**
 * @covers \rhertogh\Yii2Oauth2Server\components\server\grants\Oauth2ClientCredentialsGrant
 */
class Oauth2ClientCredentialsGrantTest extends DatabaseTestCase
{
    /**
     * @dataProvider issueAccessTokenProvider
     */
    public function testIssueAccessToken(
        $moduleConfig,
        $clientIdentifier,
        $clientCredentialsGrantUserId,
        $scopeIdentifiers,
        $expectAccessToken,
        $expectUserIdentifier,
        $expectedScopeIdentifiers,
        $expectExceptionMessage
    ) {
        $this->mockWebApplication([
            'modules' => [
                'oauth2' => $moduleConfig,
            ],
        ]);

        $module = Oauth2Module::getInstance();
        $clientCredentialsGrant = (new Oauth2ClientCredentialsGrantFactory(['module' => $module]))->getGrantType();
        $clientCredentialsGrant->setAccessTokenRepository($module->getAccessTokenRepository());
        $clientCredentialsGrant->setPrivateKey($module->getPrivateKey());

        $client = Oauth2Client::findOne(['identifier' => $clientIdentifier]);
        $client->client_credentials_grant_user_id = $clientCredentialsGrantUserId;
        $scopes = Oauth2Scope::findAll(['identifier' => $scopeIdentifiers]);

        if ($expectExceptionMessage) {
            $this->expectExceptionMessage($expectExceptionMessage);
        }

        $accessToken = $this->callInaccessibleMethod($clientCredentialsGrant, 'issueAccessToken', [
            new \DateInterval('PT1M'),
            $client,
            null,
            $scopes
        ]);

        if ($expectAccessToken) {
            $this->assertInstanceOf(Oauth2AccessTokenInterface::class, $accessToken);
            $this->assertEquals($expectUserIdentifier, $accessToken->getUserIdentifier());

            $actualScopeIdentifiers = array_map(
                fn($scope) => $scope->getIdentifier(),
                $accessToken->getScopes()
            );
            sort($expectedScopeIdentifiers);
            sort($actualScopeIdentifiers);
            $this->assertEquals($expectedScopeIdentifiers, $actualScopeIdentifiers);
        }
    }

    /**
     * @see testIssueAccessToken()
     */
    public function issueAccessTokenProvider()
    {
        return [
            [// clientCredentialsGrantUserId disabled.
                'moduleConfig' => [],
                'clientIdentifier' => 'test-client-type-client-credentials-valid',
                'clientCredentialsGrantUserId' => null,
                'scopeIdentifiers' => [],
                'expectAccessToken' => true,
                'expectUserIdentifier' => null,
                'expectedScopeIdentifiers' => [],
                'expectExceptionMessage' => null,
            ],
            [// clientCredentialsGrantUserId authorized.
                'moduleConfig' => [],
                'clientIdentifier' => 'test-client-type-client-credentials-valid',
                'clientCredentialsGrantUserId' => 123,
                'scopeIdentifiers' => [],
                'expectAccessToken' => true,
                'expectUserIdentifier' => 123,
                'expectedScopeIdentifiers' => [
                    'user.id.read', // applied by default automatically.
                ],
                'expectExceptionMessage' => null,
            ],
            [// clientCredentialsGrantUserId unauthorized client.
                'moduleConfig' => [],
                'clientIdentifier' => 'test-client-type-client-credentials-valid',
                'clientCredentialsGrantUserId' => 124,
                'scopeIdentifiers' => [],
                'expectAccessToken' => false,
                'expectUserIdentifier' => null,
                'expectedScopeIdentifiers' => [],
                'expectExceptionMessage' =>
                    'User id "124" is set as default "client credentials grant user" for client'
                    . ' "test-client-type-client-credentials-valid" but the client is not authorized for this user.',
            ],
            [// clientCredentialsGrantUserId unauthorized scope.
                'moduleConfig' => [],
                'clientIdentifier' => 'test-client-type-client-credentials-valid',
                'clientCredentialsGrantUserId' => 123,
                'scopeIdentifiers' => ['user.username.read'],
                'expectAccessToken' => true,
                'expectUserIdentifier' => 123,
                'expectedScopeIdentifiers' => [
                    'user.id.read', // applied by default automatically.
                    'user.username.read',
                ],
                'expectExceptionMessage' => null,
            ],
            [// clientCredentialsGrantUserId unauthorized scope.
                'moduleConfig' => [],
                'clientIdentifier' => 'test-client-type-client-credentials-valid',
                'clientCredentialsGrantUserId' => 123,
                'scopeIdentifiers' => ['user.email_address.read'],
                'expectAccessToken' => false,
                'expectUserIdentifier' => null,
                'expectedScopeIdentifiers' => [],
                'expectExceptionMessage' => 'User id "123" is set as default "client credentials grant user"'
                    . ' for client "test-client-type-client-credentials-valid"'
                    . ' but the following scopes are not approved: user.email_address.read',
            ],
            [// clientCredentialsGrantUserId unauthorized scope but not active for the client
             // (should silently reject scope).
                'moduleConfig' => [],
                'clientIdentifier' => 'test-client-type-client-credentials-valid',
                'clientCredentialsGrantUserId' => 123,
                'scopeIdentifiers' => ['defined-but-not-assigned'],
                'expectAccessToken' => true,
                'expectUserIdentifier' => 123,
                'expectedScopeIdentifiers' => [
                    'user.id.read', // applied by default automatically.
                ],
                'expectExceptionMessage' => null,
            ],
        ];
    }
}
