<?php

namespace Yii2Oauth2ServerTests\functional\OpenIdConnect;

use Codeception\Util\HttpCode;
use rhertogh\Yii2Oauth2Server\interfaces\components\authorization\client\Oauth2ClientAuthorizationRequestInterface;
use rhertogh\Yii2Oauth2Server\interfaces\components\openidconnect\request\Oauth2OidcAuthenticationRequestInterface;
use rhertogh\Yii2Oauth2Server\Oauth2Module;
use Yii;
use Yii2Oauth2ServerTests\_helpers\OpenIdConnectTestClient;
use Yii2Oauth2ServerTests\_helpers\TestUserModel;
use Yii2Oauth2ServerTests\ApiTester;
use Yii2Oauth2ServerTests\functional\OpenIdConnect\_base\BaseOpenIdConnectCest;

/**
 * @covers \rhertogh\Yii2Oauth2Server\controllers\web\Oauth2ServerController
 * @covers \rhertogh\Yii2Oauth2Server\controllers\web\server\Oauth2AuthorizeAction
 */
class OpenIdConnectInitiatingUserRegistrationCest extends BaseOpenIdConnectCest
{
    /**
     * Test user account creation via oidc prompt
     */
    public function openIdConnectPromptLoginTest(ApiTester $I)
    {
        $module = Oauth2Module::getInstance();

        $oauthClient = $this->getOpenIdConnectTestClient($I);

        $authorizationUrl = $oauthClient->buildAuthUrl([
            'prompt' => Oauth2OidcAuthenticationRequestInterface::REQUEST_PARAMETER_PROMPT_CREATE,
        ]);

        # region register redirect
        $I->stopFollowingRedirects();
        $I->sendGet($authorizationUrl);
        $I->seeResponseCodeIs(HttpCode::FOUND);
        $location = $I->grabHttpHeader('Location');
        $urlParts = parse_url($location);
        $I->assertArrayHasKey('path', $urlParts);
        $I->assertEquals('/user/register', $urlParts['path']);
        # endregion

        # region create user
        // Note: this is normally done by the application and outside the Yii2 Oauth2 Server, creating a dummy here.
        $user = new TestUserModel([
            'username' => 'new test user',
            'password_hash' => '$2y$10$PtIeyOB1.rPPVHjgTzCO5eSNPS1vdOCNp4nk1IvA2FKYu6jslFVNK', // "password"
            'email_address' => 'new.test.user@test.test',
            'enabled' => true,
            'created_at' => time(),
            'updated_at' => time(),
        ]);

        $I->assertTrue($user->save());
        $I->amLoggedInAs($user);

        $I->startFollowingRedirects();
        $I->sendGet(Yii::$app->user->returnUrl);
        # endregion

        # region authorize client
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
        $I->assertEquals($user->getId(), $accessToken->getParam('sub'));
        # endregion fetch access token
    }
}
