<?php

namespace Yii2Oauth2ServerTests\functional;

use Codeception\Example;
use Codeception\Util\HttpCode;
use rhertogh\Yii2Oauth2Server\interfaces\components\authorization\Oauth2ClientAuthorizationRequestInterface;
use rhertogh\Yii2Oauth2Server\Oauth2Module;
use Yii;
use yii\authclient\OpenIdConnect;
use yii\helpers\ArrayHelper;
use yii\httpclient\Client;
use Yii2Oauth2ServerTests\_helpers\ApiTesterTransport;
use Yii2Oauth2ServerTests\_helpers\OpenIdConnectTestClient;
use Yii2Oauth2ServerTests\_helpers\TestUserModelOidc;
use Yii2Oauth2ServerTests\ApiTester;
use Yii2Oauth2ServerTests\functional\_base\BaseGrantCest;

/**
 * @covers \rhertogh\Yii2Oauth2Server\controllers\web\Oauth2ServerController
 * @covers \rhertogh\Yii2Oauth2Server\controllers\web\server\Oauth2AuthorizeAction
 * @covers \rhertogh\Yii2Oauth2Server\controllers\web\server\Oauth2AccessTokenAction
 *
 * @covers \rhertogh\Yii2Oauth2Server\controllers\web\Oauth2CertificatesController
 * @covers \rhertogh\Yii2Oauth2Server\controllers\web\certificates\Oauth2JwksAction
 *
 * @covers \rhertogh\Yii2Oauth2Server\controllers\web\Oauth2WellKnownController
 * @covers \rhertogh\Yii2Oauth2Server\controllers\web\wellknown\Oauth2OpenidConfigurationAction
 *
 * @covers \rhertogh\Yii2Oauth2Server\controllers\web\Oauth2OidcController
 * @covers \rhertogh\Yii2Oauth2Server\controllers\web\openidconnect\Oauth2OidcUserinfoAction
 */
class OpenIdConnectCest extends BaseGrantCest
{
    public function _before(ApiTester $I)
    {
        parent::_before($I);

        // Using TestUserModelOidc as definition for the Oauth2Module's and user component identityClass.
        Oauth2Module::getInstance()->identityClass = TestUserModelOidc::class;
        Yii::$app->user->identityClass = TestUserModelOidc::class;
    }

    /**
     * @dataProvider openIdConnectTestProvider
     */
    public function openIdConnectTest(ApiTester $I, Example $example)
    {
        $module = Oauth2Module::getInstance();
        $module->defaultAccessTokenTTL = $example['accessTokenTTL'];
        $module->openIdConnectIssueRefreshTokenWithoutOfflineAccessScope = true;

        $oauthClient = $this->getOpenIdConnectTestClient($I, [
            'scope' => $example['scope'],
        ]);

        $authorizationUrl = $oauthClient->buildAuthUrl(['prompt' => $example['prompt']]);

        # region authorize client
        $I->amLoggedInAs(123);
        $I->startFollowingRedirects();
        $I->sendGet($authorizationUrl);
        $I->seeCurrentUrlMatches('%^/oauth2/authorize-client%');
        $I->stopFollowingRedirects();
        $I->amOnPage($I->grabFromCurrentUrl());
        $I->submitForm('#oauth2-client-authorization-request-form', [
            'Oauth2ClientAuthorizationRequest[authorizationStatus]' =>
                Oauth2ClientAuthorizationRequestInterface::AUTHORIZATION_APPROVED,
        ]);
        $location = $I->grabHttpHeader('Location');
        # endregion

        # region get authorization code
        $I->sendGet($location);
        $I->seeResponseCodeIs(HttpCode::FOUND);
        $location = $I->grabHttpHeader('Location');
        $locationParts = parse_url($location);
        $I->assertArrayHasKey('query', $locationParts);
        parse_str($locationParts['query'], $queryParts);
        $I->assertArrayHasKey('code', $queryParts);
        $code = $queryParts['code'];
        # endregion

        # region fetch access token
        $accessToken = $oauthClient->fetchAccessToken($code); // Get access token.
        foreach ($example['expectedParams'] as $expectedParamName => $expectedParamValue) {
            $I->assertEquals($expectedParamValue, $accessToken->getParam($expectedParamName));
        }
        # endregion fetch access token

        # region make authenticated request
        $this->testAuthenticatedRequest($I, $accessToken, ['id']);
        # endregion

        # region refresh access token
        $oauthClient = $this->getOpenIdConnectTestClient($I);

        TestUserModelOidc::$hasActiveSession = false;
        if (!$example['expectedOfflineAccess']) {
            try {
                $oauthClient->refreshAccessToken($accessToken);
            } catch (\Exception $e) {
            }
            $I->assertEquals(HttpCode::UNAUTHORIZED, $e->getCode());

            TestUserModelOidc::$hasActiveSession = true;
        }

        // Expected to be available (either via "offline_access" scope or `hasActiveSession == true`).
        $refreshedAccessToken = $oauthClient->refreshAccessToken($accessToken);
        $I->assertEquals(123, $refreshedAccessToken->getParam('sub'));
        # endregion

        Yii::$app->user->setIdentity(null);
        $userAttributes = $oauthClient->getUserAttributes();
        $I->assertEquals(123, $userAttributes['sub']);
    }

    /**
     * @return array[]
     * @see openIdConnectTest()
     */
    protected function openIdConnectTestProvider()
    {
        return [
            [
                'scope' => 'openid',
                'prompt' => null,
                'accessTokenTTL' => 'P100Y',
                'expectedParams' => [
                    'sub' => 123,
                    'nickname' => null,
                ],
                'expectedOfflineAccess' => false,
            ],
            [
                'scope' => 'openid profile email phone address offline_access',
                'prompt' => 'consent',
                'accessTokenTTL' => 'PT10M',
                'expectedParams' => [
                    'sub' => 123,
                    'nickname' => 'test.user',
                    'address' => [
                        'formatted' => "123 Elf Road\nXM4 5HQ, Santa's Grotto\nReindeerland, North Pole",
                        'streetAddress' => '123 Elf Road',
                        'locality' => "Santa's Grotto",
                        'region' => 'Reindeerland',
                        'postalCode' => 'XM4 5HQ',
                        'country' => 'North Pole',
                    ],
                ],
                'expectedOfflineAccess' => true,
            ],
        ];
    }

    /**
     * Test reauthentication via oidc prompt or max_age
     * @dataProvider openIdConnectPromptLoginTestProvider
     */
    public function openIdConnectPromptLoginTest(ApiTester $I, Example $example)
    {
        $module = Oauth2Module::getInstance();

        $oauthClient = $this->getOpenIdConnectTestClient($I);

        $authorizationUrl = $oauthClient->buildAuthUrl([
            'prompt' => $example['prompt'],
            'max_age' => $example['max_age'],
        ]);

        # region account selection
        $I->amLoggedInAs(123);
        $I->startFollowingRedirects();
        $I->sendGet($authorizationUrl);
        $I->seeCurrentUrlMatches('%^/site/login%');
        // get `$clientAuthorizationRequestId`.
        $urlParts = parse_url($I->grabFromCurrentUrl());
        $I->assertArrayHasKey('query', $urlParts);
        parse_str($urlParts['query'], $queryParts);
        $I->assertArrayHasKey('reauthenticate', $queryParts);
        $I->assertEquals(1, $queryParts['reauthenticate']);
        $I->assertArrayHasKey('clientAuthorizationRequestId', $queryParts);
        $clientAuthorizationRequestId = $queryParts['clientAuthorizationRequestId'];
        // get ClientAuthorizationRequest and set reauthenticated (normally this would be done in controller).
        $clientAuthorizationRequest = $module->getClientAuthReqSession($clientAuthorizationRequestId);
        $clientAuthorizationRequest->setUserAuthenticatedDuringRequest(true);
        $module->setClientAuthReqSession($clientAuthorizationRequest);
        # endregion

        # region authorize client
        $I->amLoggedInAs(123);
        $I->startFollowingRedirects();
        $I->sendGet($clientAuthorizationRequest->getAuthorizationRequestUrl());
        $I->seeCurrentUrlMatches('%^/oauth2/authorize-client%');
        $I->stopFollowingRedirects();
        $I->amOnPage($I->grabFromCurrentUrl());
        $I->submitForm('#oauth2-client-authorization-request-form', [
            'Oauth2ClientAuthorizationRequest[authorizationStatus]' =>
                Oauth2ClientAuthorizationRequestInterface::AUTHORIZATION_APPROVED,
        ]);
        $location = $I->grabHttpHeader('Location');
        # endregion

        # region get authorization code
        $I->sendGet($location);
        $I->seeResponseCodeIs(HttpCode::FOUND);
        $location = $I->grabHttpHeader('Location');
        $urlParts = parse_url($location);
        $I->assertArrayHasKey('query', $urlParts);
        parse_str($urlParts['query'], $queryParts);
        $I->assertArrayHasKey('code', $queryParts);
        $code = $queryParts['code'];
        # endregion

        # region fetch access token
        $accessToken = $oauthClient->fetchAccessToken($code); // Get access token.
        $I->assertEquals(123, $accessToken->getParam('sub'));
        # endregion fetch access token
    }

    /**
     * @return array[]
     * @see openIdConnectPromptLoginTest()
     */
    protected function openIdConnectPromptLoginTestProvider()
    {
        return [
            [
                'prompt' => 'login',
                'max_age' => null,
            ],
            [
                'prompt' => null,
                'max_age' => 60,
            ],
        ];
    }

    /**
     * Test account selection via oidc prompt
     */
    public function openIdConnectPromptSelectAccountTest(ApiTester $I)
    {
        $module = Oauth2Module::getInstance();

        $oauthClient = $this->getOpenIdConnectTestClient($I);

        $authorizationUrl = $oauthClient->buildAuthUrl([
            'prompt' => 'select_account',
        ]);

        # region account selection
        $I->amLoggedInAs(123); // Note: using test.user as identity.
        $I->startFollowingRedirects();
        $I->sendGet($authorizationUrl);
        $I->seeCurrentUrlMatches('%^/site/account-selection%');
        // get `$clientAuthorizationRequestId`.
        $urlParts = parse_url($I->grabFromCurrentUrl());
        $I->assertArrayHasKey('query', $urlParts);
        parse_str($urlParts['query'], $queryParts);
        $I->assertArrayHasKey('clientAuthorizationRequestId', $queryParts);
        $clientAuthorizationRequestId = $queryParts['clientAuthorizationRequestId'];
        // get ClientAuthorizationRequest and set identity (normally this would be done in controller).
        $clientAuthorizationRequest = $module->getClientAuthReqSession($clientAuthorizationRequestId);
        // Note: using test.user2 as identity.
        $clientAuthorizationRequest->setUserIdentity(TestUserModelOidc::findIdentity(124));
        $module->setClientAuthReqSession($clientAuthorizationRequest);
        # endregion

        # region authorize client
        $I->amLoggedInAs(123); // Note: using test.user as identity.
        $I->startFollowingRedirects();
        $I->sendGet($clientAuthorizationRequest->getAuthorizationRequestUrl());
        $I->seeCurrentUrlMatches('%^/oauth2/authorize-client%');
        $I->stopFollowingRedirects();
        $I->amOnPage($I->grabFromCurrentUrl());
        $I->submitForm('#oauth2-client-authorization-request-form', [
            'Oauth2ClientAuthorizationRequest[authorizationStatus]' =>
                Oauth2ClientAuthorizationRequestInterface::AUTHORIZATION_APPROVED,
        ]);
        $location = $I->grabHttpHeader('Location');
        # endregion

        # region get authorization code
        $I->sendGet($location);
        $I->seeResponseCodeIs(HttpCode::FOUND);
        $location = $I->grabHttpHeader('Location');
        $urlParts = parse_url($location);
        $I->assertArrayHasKey('query', $urlParts);
        parse_str($urlParts['query'], $queryParts);
        $I->assertArrayHasKey('code', $queryParts);
        $code = $queryParts['code'];
        # endregion

        $accessToken = $oauthClient->fetchAccessToken($code); // Get access token.

        // Expect test.user2 as subject.
        $I->assertEquals(124, $accessToken->getParam('sub'));
    }

    protected function getOpenIdConnectTestClient($I, $config = [])
    {
        return new OpenIdConnect(ArrayHelper::merge(
            [
                'httpClient' => [
                    'class' => Client::class,
                    'transport' => [
                        'class' => ApiTesterTransport::class,
                        'apiTester' => $I,
                    ]
                ],
                'validateAuthNonce' => true,
                'returnUrl' => 'http://localhost/redirect_uri/',
                'issuerUrl' => 'https://localhost',
                'clientId' => 'test-client-type-auth-code-open-id-connect',
                'enablePkce' => true,
                'scope' => 'openid'
            ],
            $config,
        ));
    }
}
