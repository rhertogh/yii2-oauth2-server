<?php

namespace Yii2Oauth2ServerTests\functional\OpenIdConnect;

use Codeception\Example;
use Codeception\Util\HttpCode;
use rhertogh\Yii2Oauth2Server\interfaces\components\authorization\client\Oauth2ClientAuthorizationRequestInterface;
use rhertogh\Yii2Oauth2Server\Oauth2Module;
use Yii;
use yii\authclient\OpenIdConnect;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\httpclient\Client;
use Yii2Oauth2ServerTests\_helpers\ApiTesterTransport;
use Yii2Oauth2ServerTests\_helpers\OpenIdConnectTestClient;
use Yii2Oauth2ServerTests\_helpers\TestUserModelOidc;
use Yii2Oauth2ServerTests\ApiTester;
use Yii2Oauth2ServerTests\functional\_base\BaseGrantCest;
use Yii2Oauth2ServerTests\functional\OpenIdConnect\_base\BaseOpenIdConnectCest;

/**
 * @covers \rhertogh\Yii2Oauth2Server\controllers\web\Oauth2ServerController
 * @covers \rhertogh\Yii2Oauth2Server\controllers\web\openidconnect\Oauth2OidcEndSessionAction
 * @covers \rhertogh\Yii2Oauth2Server\controllers\web\consent\Oauth2AuthorizeEndSessionAction
 */
class OpenIdConnectRpInitiatedLogoutCest extends BaseOpenIdConnectCest
{
    public function openIdConnectRpInitiatedLogoutTest(ApiTester $I)
    {
        $module = Oauth2Module::getInstance();

        $oauthClient = $this->getOpenIdConnectTestClient($I);

        $authorizationUrl = $oauthClient->buildAuthUrl();

        # region authorize client
        $I->amLoggedInAs(123);

        // Ensure we have an id in the app, so we can validate the logout later.
        $I->assertEquals(123, Yii::$app->user->getId());

        $I->startFollowingRedirects();
        $I->sendGet($authorizationUrl);
        $I->stopFollowingRedirects();
        $I->amOnPage($I->grabFromCurrentUrl());
        $I->submitForm('#oauth2-client-authorization-request-form', [
            'Oauth2ClientAuthorizationRequest[authorizationStatus]' =>
                Oauth2ClientAuthorizationRequestInterface::AUTHORIZATION_APPROVED,
        ]);
        $I->seeResponseCodeIs(HttpCode::FOUND);
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
        $accessToken = $oauthClient->fetchAccessToken($code);
        $idToken = $accessToken->getParam('id_token');
        # endregion fetch access token

        $state = 'testState';
        $url = Url::to([
            'openid-connect/end-session',
            'id_token_hint' => $idToken,
            'client_id' => $oauthClient->clientId,
            'state' => $state,
            'post_logout_redirect_uri' => 'http://localhost/logout_redirect_uri/'
        ]);
        $I->startFollowingRedirects();
        $I->sendGet($url);
        $I->stopFollowingRedirects();
        $I->amOnPage($I->grabFromCurrentUrl());
        $I->submitForm('#oauth2-end-session-authorization-request-form', [
            'Oauth2EndSessionAuthorizationRequest[authorizationStatus]' =>
                Oauth2ClientAuthorizationRequestInterface::AUTHORIZATION_APPROVED,
        ]);

        $I->seeResponseCodeIs(HttpCode::FOUND);
        $location = $I->grabHttpHeader('Location');
        $I->sendGet($location);
        $I->seeResponseCodeIs(HttpCode::FOUND);
        $location = $I->grabHttpHeader('Location');

        $locationParts = parse_url($location);
        $I->assertArrayHasKey('path', $locationParts);
        $I->assertEquals('/logout_redirect_uri/', $locationParts['path']);
        parse_str($locationParts['query'], $queryParts);
        $I->assertArrayHasKey('state', $queryParts);
        $I->assertEquals($state, $queryParts['state']);

        $I->assertNull(Yii::$app->user->getId());
    }
}
