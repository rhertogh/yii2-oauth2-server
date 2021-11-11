<?php
namespace Yii2Oauth2ServerTests\functional;

use Codeception\Example;
use Codeception\Util\HttpCode;
use League\OAuth2\Client\Token\AccessToken;
use rhertogh\Yii2Oauth2Server\helpers\UrlHelper;
use rhertogh\Yii2Oauth2Server\interfaces\components\authorization\Oauth2ClientAuthorizationRequestInterface;
use rhertogh\Yii2Oauth2Server\Oauth2Module;
use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use Yii2Oauth2ServerTests\_helpers\ClientTokenProvider;
use Yii2Oauth2ServerTests\_helpers\TestUserModel;
use Yii2Oauth2ServerTests\functional\_base\BaseGrantCest;
use Yii2Oauth2ServerTests\ApiTester;

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
        ]);

        $token = new AccessToken(['access_token' => 'Bearer invalid']);
        $authenticatedRequest = $provider->getAuthenticatedRequest('GET', 'http://localhost/test/api/me', $token);
        $I->sendPsr7Request($authenticatedRequest);
        $I->seeResponseCodeIs(HttpCode::UNAUTHORIZED);
    }
}
