<?php

namespace Yii2Oauth2ServerTests\functional;

use Codeception\Util\HttpCode;
use League\OAuth2\Client\Token\AccessToken;
use Yii2Oauth2ServerTests\_helpers\ClientTokenProvider;
use Yii2Oauth2ServerTests\ApiTester;
use Yii2Oauth2ServerTests\functional\_base\BaseGrantCest;

/**
 * Ensure we can't access the test API without authorization
 */
class UnauthorizedApiRequestCest extends BaseGrantCest
{
    public function unauthorizedApiRequestTest(ApiTester $I)
    {
        $provider = $this->getProvider([
            'clientId' => 'test-client-type-auth-code-valid',
            'clientSecret' => 'secret',
            'pkceMethod' => ClientTokenProvider::PKCE_METHOD_S256,
            'redirectUri' => 'http://localhost/redirect_uri/',
        ]);

        $token = new AccessToken(['access_token' => 'Bearer invalid']);
        $authenticatedRequest = $provider->getAuthenticatedRequest('GET', 'http://localhost/test/api/me', $token);
        $I->sendPsr7Request($authenticatedRequest);
        $I->seeResponseCodeIs(HttpCode::UNAUTHORIZED);
        $I->seeHttpHeader('Content-Type', 'application/json; charset=UTF-8');
    }
}
