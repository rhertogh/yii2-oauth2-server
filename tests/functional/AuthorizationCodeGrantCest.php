<?php

namespace Yii2Oauth2ServerTests\functional;

use Codeception\Example;
use Codeception\Util\HttpCode;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Signer\Rsa\Sha256;
use League\OAuth2\Client\Token\AccessToken;
use rhertogh\Yii2Oauth2Server\interfaces\components\authorization\Oauth2ClientAuthorizationRequestInterface;
use rhertogh\Yii2Oauth2Server\Oauth2Module;
use Yii;
use yii\helpers\Json;
use Yii2Oauth2ServerTests\_helpers\ClientTokenProvider;
use Yii2Oauth2ServerTests\ApiTester;
use Yii2Oauth2ServerTests\functional\_base\BaseGrantCest;

/**
 * @covers \rhertogh\Yii2Oauth2Server\controllers\web\server\Oauth2AuthorizeAction
 * @covers \rhertogh\Yii2Oauth2Server\controllers\web\server\Oauth2AccessTokenAction
 * @covers \rhertogh\Yii2Oauth2Server\controllers\web\Oauth2ServerController
 */
class AuthorizationCodeGrantCest extends BaseGrantCest
{

    /**
     * @dataProvider authorizationCodeGrantTestProvider
     */
    public function authorizationCodeGrantTest(ApiTester $I, Example $example)
    {
        $module = Oauth2Module::getInstance();
        $module->defaultAccessTokenTTL = $example['accessTokenTTL'];

        $jwtConfiguration = Configuration::forSymmetricSigner(
            new Sha256(),
            InMemory::plainText('')
        );

        $provider = $this->getProvider([
            'clientId' => 'test-client-type-auth-code-valid',
            'clientSecret' => 'secret',
            'pkceMethod' => ClientTokenProvider::PKCE_METHOD_S256,
        ]);

        $scope = [];
        $expectedProperties = ['id'];
        foreach ($example['scopeFields'] as $scopeField) {
            $scope[] = 'user.' . $scopeField . '.read';
            $expectedProperties[] = $scopeField;
        }

        $authorizationUrl = $provider->getAuthorizationUrl([
            'scope' => $scope,
        ]);

        $expectedState = $provider->getState();
        $pkceCode = $provider->getPkceCode();

        # region login redirect
        $I->stopFollowingRedirects();
        $I->sendGet($authorizationUrl);
        $I->seeResponseCodeIs(HttpCode::FOUND);
        $I->seeHttpHeader('Location', 'http://localhost/site/login');
        # endregion

        # region authorize client
        $I->amLoggedInAs(123); // The yii2-oauth2-server does not contain a login screen, setting logged-in user.
        $I->startFollowingRedirects();
        $I->sendGet(Yii::$app->user->returnUrl);
        $I->seeCurrentUrlMatches('%^/oauth2/authorize-client%');
        $I->stopFollowingRedirects();
        $I->amOnPage($I->grabFromCurrentUrl());
        $I->submitForm('#oauth2-client-authorization-request-form', [
            'Oauth2ClientAuthorizationRequest[authorizationStatus]' =>
                Oauth2ClientAuthorizationRequestInterface::AUTHORIZATION_APPROVED,
        ]);
        $location = $I->grabHttpHeader('Location');
        # endregion

        // Simulate re-initialization of provider.
        $provider = $this->getProvider([
            'clientId' => 'test-client-type-auth-code-valid',
            'clientSecret' => 'secret',
            'pkceMethod' => ClientTokenProvider::PKCE_METHOD_S256,
        ]);

        $provider->setPkceCode($pkceCode);

        # region get authorization code
        $I->sendGet($location);
        $I->seeResponseCodeIs(HttpCode::FOUND);
        $location = $I->grabHttpHeader('Location');
        $locationParts = parse_url($location);
        $I->assertArrayHasKey('query', $locationParts);
        parse_str($locationParts['query'], $queryParts);
        $I->assertArrayHasKey('code', $queryParts);
        $I->assertArrayHasKey('state', $queryParts);
        [
            'code' => $code,
            'state' => $state,
        ] = $queryParts;
        $I->assertEquals('/redirect_uri/', $locationParts['path']);
        $I->assertEquals($expectedState, $state);
        $I->assertTrue(strlen($code) > 100, 'Expected a `code` with at leas 100 characters.');
        # endregion

        # region get access token
        $accessTokenRequest = $provider->getAccessTokenRequestWrapper('authorization_code', [
            'code' => $code,
        ]);


        $expirationTime = (new \DateTimeImmutable('@' . time())) // ensure no micro seconds.
            ->add(new \DateInterval($example['accessTokenTTL']));
        $I->sendPsr7Request($accessTokenRequest);
        $this->validateAccessTokenResponse($I);

        $response = Json::decode($I->grabResponse());
        $accessToken = new AccessToken($response);
        $I->assertFalse($accessToken->hasExpired());
        $token = $jwtConfiguration->parser()->parse($accessToken->getToken());
        $exp = $token->claims()->get('exp');
        $I->assertGreaterThanOrEqual($expirationTime, $exp);
        $I->assertLessThanOrEqual($expirationTime->modify('+1 second'), $exp);
        # endregion

        # region refresh access token
        $accessTokenRequest = $provider->getAccessTokenRequestWrapper('refresh_token', [
            'refresh_token' => $accessToken->getRefreshToken(),
        ]);

        $I->sendPsr7Request($accessTokenRequest);
        $this->validateAccessTokenResponse($I);
        # endregion

        # region make authenticated request
        $token = new AccessToken(Json::decode($I->grabResponse()));
        $this->testAuthenticatedRequest($I, $token, $expectedProperties);
        # endregion
    }

    /**
     * @return array[]
     * @see authorizationCodeGrantTest()
     */
    protected function authorizationCodeGrantTestProvider()
    {
        return [
            [
                'accessTokenTTL' => 'P1Y',
                'scopeFields' => [
                    'username',
                    'email_address',
                ],
            ],
            [
                'accessTokenTTL' => 'PT10M',
                'scopeFields' => [
                    'username',
                    'email_address',
                    'enabled',
                    'created_at',
                    'updated_at',
                ],
            ],
        ];
    }
}
