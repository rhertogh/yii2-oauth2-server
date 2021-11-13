<?php

namespace Yii2Oauth2ServerTests\functional;

use Codeception\Example;
use Codeception\Util\HttpCode;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Signer\Rsa\Sha256;
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
 * ToDo: specify file coverage when it's possible to specify files:
 * https://github.com/sebastianbergmann/phpunit/issues/3794
 */
class ClientAuthorizationCest extends BaseGrantCest
{
    public function newClientWithScopeAuthorizationTest(ApiTester $I)
    {
        $provider = $this->getProvider([
            'clientId' => 'test-client-type-auth-code-valid',
            'clientSecret' => 'secret',
            'pkceMethod' => ClientTokenProvider::PKCE_METHOD_S256,
        ]);

        $authorizationUrl = $provider->getAuthorizationUrl([
            'scope' => [
                'user.username.read',
            ],
        ]);

        # region authorize client
        $I->amLoggedInAs(123); // The yii2-oauth2-server does not contain a login screen, setting logged-in user.
        $I->startFollowingRedirects();
        $I->sendGet($authorizationUrl);
        $I->seeCurrentUrlMatches('%^/oauth2/authorize-client%');
        $I->stopFollowingRedirects();
        $I->amOnPage($I->grabFromCurrentUrl());
        $I->seeElement('#oauth2-client-authorization-request-form');
        $I->see('See your user id');
        $I->seeElement('#oauth2clientauthorizationrequest-selectedscopeidentifiers-user_id_read[type="hidden"]');
        $I->seeElement('#oauth2clientauthorizationrequest-selectedscopeidentifiers-applied_by_default_by_client_not_required_for_client[type="checkbox"]');
        # endregion authorize client
    }

    public function newClientWithoutScopeAuthorizationTest(ApiTester $I)
    {
        $provider = $this->getProvider([
            'clientId' => 'test-client-type-auth-code-no-scopes',
            'pkceMethod' => ClientTokenProvider::PKCE_METHOD_S256,
        ]);

        $authorizationUrl = $provider->getAuthorizationUrl();

        # region authorize client
        $I->amLoggedInAs(123); // The yii2-oauth2-server does not contain a login screen, setting logged-in user.
        $I->startFollowingRedirects();
        $I->sendGet($authorizationUrl);
        $I->seeCurrentUrlMatches('%^/oauth2/authorize-client%');
        $I->stopFollowingRedirects();
        $I->amOnPage($I->grabFromCurrentUrl());
        $I->seeElement('#oauth2-client-authorization-request-form');
        $I->see('Valid public client with Grant Type Auth Code without scopes would like to access Yii2 Oauth2 Server Test on your behalf.');
        # endregion authorize client
    }

    public function additionalScopesAuthorizationTest(ApiTester $I)
    {
        $provider = $this->getProvider([
            'clientId' => 'test-client-type-auth-code-valid',
            'clientSecret' => 'secret',
            'pkceMethod' => ClientTokenProvider::PKCE_METHOD_S256,
        ]);

        $authorizationUrl = $provider->getAuthorizationUrl([
            'scope' => [
                'pre-assigned-for-user-test',
                'user.username.read',
            ],
        ]);

        # region authorize client
        $I->amLoggedInAs(124); // The yii2-oauth2-server does not contain a login screen, setting logged-in user.
        $I->startFollowingRedirects();
        $I->sendGet($authorizationUrl);
        $I->seeCurrentUrlMatches('%^/oauth2/authorize-client%');
        $I->stopFollowingRedirects();
        $I->amOnPage($I->grabFromCurrentUrl());
        $I->seeElement('#oauth2-client-authorization-request-form');
        $I->see('See your user id');
        $I->seeElement('#oauth2clientauthorizationrequest-selectedscopeidentifiers-pre_assigned_for_user_test[type="hidden"]');
        # endregion authorize client
    }
}
