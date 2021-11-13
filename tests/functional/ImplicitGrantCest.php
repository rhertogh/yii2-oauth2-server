<?php

namespace Yii2Oauth2ServerTests\functional;

use Codeception\Example;
use Codeception\Util\HttpCode;
use League\OAuth2\Client\Token\AccessToken;
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
 * @covers \rhertogh\Yii2Oauth2Server\controllers\web\server\Oauth2AuthorizeAction
 * @covers \rhertogh\Yii2Oauth2Server\controllers\web\Oauth2ServerController
 */
class ImplicitGrantCest extends BaseGrantCest
{
    public function authorizationCodeGrantTest(ApiTester $I)
    {
        $provider = $this->getProvider([
            'clientId' => 'test-client-type-implicit-valid',
            'clientSecret' => 'secret',
        ]);

        $authorizationUrl = $provider->getAuthorizationUrl([
            'response_type' => 'token',
        ]);

        $expectedState = $provider->getState();

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

        # region get access token
        $I->sendGet($location);
        $location = $I->grabHttpHeader('Location');
        $locationParts = parse_url($location);
        $I->assertArrayHasKey('fragment', $locationParts);
        parse_str($locationParts['fragment'], $fragmentParts);
        $I->assertArrayHasKey('access_token', $fragmentParts);
        $I->assertArrayHasKey('token_type', $fragmentParts);
        $I->assertArrayHasKey('expires_in', $fragmentParts);
        $I->assertArrayHasKey('state', $fragmentParts);
        $I->assertEquals($expectedState, $fragmentParts['state']);
        $I->assertTrue(strlen($fragmentParts['access_token']) > 100, 'Expected an `access_token` with at leas 100 characters.');
        # endregion

        # region make authenticated request
        $token = new AccessToken($fragmentParts);
        $this->testAuthenticatedRequest($I, $token, ['id']);
        # endregion
    }
}
