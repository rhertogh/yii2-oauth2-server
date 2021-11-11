<?php

namespace Yii2Oauth2ServerTests\unit\controllers\web\openidconnect;

use Codeception\Util\HttpCode;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Signer\Rsa\Sha256;
use rhertogh\Yii2Oauth2Server\components\authorization\Oauth2ClientAuthorizationRequest;
use rhertogh\Yii2Oauth2Server\controllers\web\Oauth2ConsentController;
use rhertogh\Yii2Oauth2Server\controllers\web\consent\Oauth2AuthorizeClientAction;
use rhertogh\Yii2Oauth2Server\controllers\web\Oauth2OidcController;
use rhertogh\Yii2Oauth2Server\controllers\web\openidconnect\Oauth2OidcUserinfoAction;
use rhertogh\Yii2Oauth2Server\interfaces\components\authorization\Oauth2ClientAuthorizationRequestInterface;
use rhertogh\Yii2Oauth2Server\interfaces\controllers\web\Oauth2ConsentControllerInterface;
use rhertogh\Yii2Oauth2Server\interfaces\controllers\web\Oauth2OidcControllerInterface;
use rhertogh\Yii2Oauth2Server\models\Oauth2Client;
use rhertogh\Yii2Oauth2Server\Oauth2Module;
use Yii;
use yii\web\Response;
use Yii2Oauth2ServerTests\_helpers\TestUserModel;
use Yii2Oauth2ServerTests\_helpers\TestUserModelOidc;
use Yii2Oauth2ServerTests\unit\DatabaseTestCase;
use Yii2Oauth2ServerTests\unit\TestCase;

/**
 * @covers \rhertogh\Yii2Oauth2Server\controllers\web\openidconnect\Oauth2OidcUserinfoAction
 */
class Oauth2OidcUserinfoActionTest extends DatabaseTestCase
{
    protected function getMockController()
    {
        return new Oauth2OidcController(Oauth2OidcControllerInterface::CONTROLLER_NAME, Oauth2Module::getInstance());
    }

    /**
     * @dataProvider runUserInfoAlgProvider
     */
    public function testRunUserInfoAlg($userInfoAlg, $nonce, $expectExceptionMessage)
    {
        $clientIdentifier = 'test-client-type-auth-code-open-id-connect';
        Oauth2Client::updateAll(['oidc_userinfo_encrypted_response_alg' => $userInfoAlg], ['identifier' => $clientIdentifier]);

        $this->mockWebApplication();
        $module = Oauth2Module::getInstance();
        $user = TestUserModelOidc::findOne(123);
        $controller = $this->getMockController();
        $userinfoAction = new Oauth2OidcUserinfoAction('Oauth2OidcUserinfoActionTest', $controller);

        $authHeaderDummy = 'abc';
        $authClaims = [
            'oauth_user_id' => $user->getIdentifier(),
            'oauth_scopes' => ['openid', 'profile'],
            'oauth_access_token_id' => null, // not used during test
            'oauth_client_id' => $clientIdentifier,
        ];

        Yii::$app->request->queryParams = ['nonce' => $nonce];
        Yii::$app->request->headers->set('Authorization', $authHeaderDummy);
        $this->setInaccessibleProperty($module, '_oauthClaimsAuthorizationHeader', $authHeaderDummy);
        $this->setInaccessibleProperty($module, '_oauthClaims', $authClaims);
        Yii::$app->user->setIdentity($user);

        if ($expectExceptionMessage) {
            $this->expectExceptionMessage($expectExceptionMessage);
        }
        $response = $userinfoAction->run();

        $this->assertEquals(HttpCode::OK, $response->getStatusCode());
        if (empty($userInfoAlg)) {
            $responseData = $response->data;
        } elseif ($userInfoAlg == 'RS256') {
            $jwtConfiguration = Configuration::forSymmetricSigner(
                new Sha256(),
                InMemory::plainText('')
            );
            $token = $jwtConfiguration->parser()->parse($response->data);
            $responseData = [
                'sub' => $token->claims()->get('sub'),
                'nonce' => $token->claims()->get('nonce'),
            ];
        } else {
            throw new \LogicException('Test not implemented for $userInfoAlg "' . $userInfoAlg . '".');
        }

        $this->assertEquals($user->getIdentifier(), $responseData['sub']);
        if ($nonce) {
            $this->assertEquals($nonce, $responseData['nonce']);
        }
    }

    /**
     * @see testRunUserInfoAlg()
     * @return array[]
     */
    public function runUserInfoAlgProvider()
    {
        return [
            [
                null,
                null,
                null,
            ],
            [
                null,
                'test-nonce',
                null,
            ],
            [
                'RS256',
                'test-nonce',
                null,
            ],
            [
                'non-existing',
                null,
                'Unknown userinfo response algorithm "non-existing".'
            ],
        ];
    }

    public function testRunOpenidConnectDisabled()
    {
        $this->mockWebApplication([
            'modules' => [
                'oauth2' => [
                    'enableOpenIdConnect' => false,
                ],
            ],
        ]);
        $controller = $this->getMockController();
        $userinfoAction = new Oauth2OidcUserinfoAction('Oauth2OidcUserinfoActionTest', $controller);

        $this->expectExceptionMessage('OpenID Connect is disabled.');
        $userinfoAction->run();
    }

    public function testRunMissingOpenidScope()
    {
        $this->mockWebApplication();
        $controller = $this->getMockController();
        $userinfoAction = new Oauth2OidcUserinfoAction('Oauth2OidcUserinfoActionTest', $controller);

        $this->expectExceptionMessage('Request authentication does not contain the required OpenID Connect "openid" scope.');
        $userinfoAction->run();
    }

    public function testRunMissingClient()
    {
        $this->mockWebApplication();
        $module = Oauth2Module::getInstance();
        $user = TestUserModel::findOne(123);
        $controller = $this->getMockController();
        $userinfoAction = new Oauth2OidcUserinfoAction('Oauth2OidcUserinfoActionTest', $controller);

        $authHeaderDummy = 'abc';
        $authClaims = [
            'oauth_user_id' => $user->getIdentifier(),
            'oauth_scopes' => ['openid'],
            'oauth_access_token_id' => null, // not used during test
            'oauth_client_id' => 'nope', // non-existing client
        ];

        Yii::$app->request->headers->set('Authorization', $authHeaderDummy);
        $this->setInaccessibleProperty($module, '_oauthClaimsAuthorizationHeader', $authHeaderDummy);
        $this->setInaccessibleProperty($module, '_oauthClaims', $authClaims);
        Yii::$app->user->setIdentity($user);

        $this->expectExceptionMessage('Client "nope" not found or disabled.');
        $userinfoAction->run();
    }


}
