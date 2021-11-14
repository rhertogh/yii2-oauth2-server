<?php

namespace Yii2Oauth2ServerTests\functional\exceptions;

use Codeception\Util\HttpCode;
use League\OAuth2\Server\Oauth2AuthorizationServerInterface;
use rhertogh\Yii2Oauth2Server\Oauth2Module;
use Yii;
use Yii2Oauth2ServerTests\ApiTester;
use Yii2Oauth2ServerTests\functional\_base\BaseGrantCest;

/**
 * @covers \rhertogh\Yii2Oauth2Server\controllers\web\server\Oauth2AccessTokenAction
 * @covers \rhertogh\Yii2Oauth2Server\controllers\web\server\Oauth2AuthorizeAction
 */
class Oauth2ServerHttpExceptionCest extends BaseGrantCest
{
    public function _before(ApiTester $I)
    {
        parent::_before($I);

        Yii::$container->set(Oauth2AuthorizationServerInterface::class, get_class(new class {
            public function __construct($test = null)
            {
                if ($test) {
                    throw new \Exception();
                }
            }
        }));
    }

    public function _after()
    {
        Yii::$container->clear(Oauth2AuthorizationServerInterface::class);
    }

    public function accessTokenActionOAuthServerExceptionTest(ApiTester $I)
    {
        $provider = $this->getProvider([
            'clientId' => 'test-client-type-client-credentials-valid'
        ]);

        $accessTokenRequest = $provider->getAccessTokenRequestWrapper(
            Oauth2Module::GRANT_TYPE_IDENTIFIER_CLIENT_CREDENTIALS
        );
        $I->sendPsr7Request($accessTokenRequest);
        $I->seeResponseCodeIs(HttpCode::UNAUTHORIZED);
    }

    public function authorizeActionOAuthServerExceptionTest(ApiTester $I)
    {
        $provider = $this->getProvider([
            'clientId' => 'test-client-type-auth-code-valid',
        ]);

        $I->sendGet($provider->getAuthorizationUrl());
        $I->seeResponseCodeIs(HttpCode::BAD_REQUEST); // pkceMethod missing.
    }
}
