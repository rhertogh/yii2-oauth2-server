<?php

namespace Yii2Oauth2ServerTests\unit\controllers\web\server;

use Codeception\Util\HttpCode;
use League\OAuth2\Server\Exception\OAuthServerException;
use rhertogh\Yii2Oauth2Server\components\authorization\client\Oauth2ClientAuthorizationRequest;
use rhertogh\Yii2Oauth2Server\controllers\web\server\Oauth2AuthorizeAction;
use rhertogh\Yii2Oauth2Server\exceptions\Oauth2ServerHttpException;
use rhertogh\Yii2Oauth2Server\interfaces\components\openidconnect\user\Oauth2OidcUserComponentInterface;
use rhertogh\Yii2Oauth2Server\models\Oauth2Client;
use rhertogh\Yii2Oauth2Server\Oauth2Module;
use Yii;
use yii\base\InvalidCallException;
use yii\web\Controller;
use yii\web\Response;
use yii\web\User as UserComponent;
use Yii2Oauth2ServerTests\_helpers\TestUserComponent;
use Yii2Oauth2ServerTests\_helpers\TestUserModel;
use Yii2Oauth2ServerTests\_helpers\TestUserModelOidc;
use Yii2Oauth2ServerTests\unit\DatabaseTestCase;

/**
 * @covers \rhertogh\Yii2Oauth2Server\controllers\web\server\Oauth2AuthorizeAction
 */
class Oauth2AuthorizeActionTest extends DatabaseTestCase
{
    public function testRunGuest()
    {
        $this->mockWebApplication();
        $module = Oauth2Module::getInstance();

        $controller = new Controller('test', $module);
        $accessTokenAction = new Oauth2AuthorizeAction('test', $controller);

        Yii::$app->request->setQueryParams([
            'response_type' => 'code',
            'client_id' => 'test-client-type-password-public-valid',
            'state' => '12345',
            'scope' => 'user.username.read user.email_address.read',
            'redirect_uri' => 'http://localhost/redirect_uri/',
            'code_challenge' => 'X9lWKyT6K_fuuWtp8Ij5HG9urPGas7v0Z2RGwisf49c',
            'code_challenge_method' => 'S256',
        ]);

        $response = $accessTokenAction->run();

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(HttpCode::FOUND, $response->statusCode);
        $this->assertEquals('http://localhost/site/login', $response->headers->get('location'));
    }

    public function testRunMissingPKCE()
    {
        $this->mockWebApplication();
        $module = Oauth2Module::getInstance();

        $controller = new Controller('test', $module);
        $accessTokenAction = new Oauth2AuthorizeAction('test', $controller);

        // phpcs:disable Generic.Files.LineLength.TooLong -- readability actually better on single line
        Yii::$app->request->setQueryParams([
            'response_type' => 'code',
            'client_id' => 'test-client-type-auth-code-valid', // Note, using `confidential` client, public clients always require a code challenge.
            'secret' => 'secret',
            'state' => '12345',
            'scope' => 'user.username.read user.email_address.read',
            'redirect_uri' => 'http://localhost/redirect_uri/',
        ]);
        // phpcs:enable Generic.Files.LineLength.TooLong

        $response = $accessTokenAction->run();
        $this->assertEquals(HttpCode::BAD_REQUEST, $response->statusCode);
        $this->assertEquals('Bad Request', $response->data['error']);
        $this->assertStringContainsString(
            'PKCE is required for this client when using grant type "authorization_code".',
            $response->data['error_description']
        );
    }

    public function testRunInvalidPlainPKCE()
    {
        $this->mockWebApplication();
        $module = Oauth2Module::getInstance();

        $controller = new Controller('test', $module);
        $accessTokenAction = new Oauth2AuthorizeAction('test', $controller);

        Yii::$app->request->setQueryParams([
            'response_type' => 'code',
            'client_id' => 'test-client-type-password-public-valid',
            'state' => '12345',
            'scope' => 'user.username.read user.email_address.read',
            'redirect_uri' => 'http://localhost/redirect_uri/',
            'code_challenge' => 'X9lWKyT6K_fuuWtp8Ij5HG9urPGas7v0Z2RGwisf49c',
            'code_challenge_method' => 'plain',
        ]);

        $response = $accessTokenAction->run();
        $this->assertEquals(HttpCode::BAD_REQUEST, $response->statusCode);
        $this->assertEquals('Bad Request', $response->data['error']);
        $this->assertStringContainsString(
            'PKCE code challenge mode "plain" is not allowed.',
            $response->data['error_description']
        );
    }

    public function testRunAuthenticatedUser()
    {
        $this->mockWebApplication();
        $module = Oauth2Module::getInstance();

        Yii::$app->user->setIdentity(TestUserModel::findOne(123)); // 'test.user'.
        $controller = new Controller('test', $module);
        $accessTokenAction = new Oauth2AuthorizeAction('test', $controller);

        Yii::$app->request->setQueryParams([
            'response_type' => 'code',
            'client_id' => 'test-client-type-password-public-valid',
            'state' => '12345',
            'scope' => 'user.username.read user.email_address.read',
            'redirect_uri' => 'http://localhost/redirect_uri/',
            'code_challenge' => 'X9lWKyT6K_fuuWtp8Ij5HG9urPGas7v0Z2RGwisf49c',
            'code_challenge_method' => 'S256',
        ]);

        $response = $accessTokenAction->run();

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(HttpCode::FOUND, $response->statusCode);
        $this->assertStringStartsWith('http://localhost/oauth2/authorize-client', $response->headers->get('location'));
        $this->assertStringContainsString('clientAuthorizationRequestId=', $response->headers->get('location'));
    }

    public function testRunAuthenticatedUserClientApproved()
    {
        $this->mockWebApplication();
        $module = Oauth2Module::getInstance();

        $user = TestUserModel::findOne(123); // 'test.user'.
        $clientIdentifier = 'test-client-type-password-public-valid';
        $ScopeIdentifiers = ['user.username.read', 'user.email_address.read'];
        $state = '12345';
        $redirectUri = 'http://localhost/redirect_uri/';
        Yii::$app->user->setIdentity($user);
        $controller = new Controller('test', $module);
        $accessTokenAction = new Oauth2AuthorizeAction('test', $controller);

        Yii::$app->request->setQueryParams([
            'response_type' => 'code',
            'client_id' => $clientIdentifier,
            'state' => $state,
            'scope' => implode(' ', $ScopeIdentifiers),
            'redirect_uri' => $redirectUri,
            'code_challenge' => 'X9lWKyT6K_fuuWtp8Ij5HG9urPGas7v0Z2RGwisf49c',
            'code_challenge_method' => 'S256',
        ]);

        $clientAuthorizationRequest = new Oauth2ClientAuthorizationRequest([
            'module' => $module,
            'clientIdentifier' => $clientIdentifier,
            'userIdentity' => $user,
            'requestedScopeIdentifiers' => $ScopeIdentifiers,
            'grantType' => Oauth2Module::GRANT_TYPE_AUTH_CODE,
            'authorizeUrl' => 'http://localhost/oauth2/authorize-client',
            'redirectUri' => $redirectUri,
            'state' => $state,
        ]);
        $module->setClientAuthReqSession($clientAuthorizationRequest);

        $response = $accessTokenAction->run($clientAuthorizationRequest->getRequestId());

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(HttpCode::FOUND, $response->statusCode);
        $this->assertStringStartsWith('http://localhost/oauth2/authorize-client', $response->headers->get('location'));
        $this->assertStringContainsString('clientAuthorizationRequestId=', $response->headers->get('location'));
    }

    public function testRunAuthenticatedUserIncompleteClientAuthorizationRequest()
    {
        $this->mockWebApplication();
        $module = Oauth2Module::getInstance();

        $user = TestUserModel::findOne(123); // 'test.user'.
        $clientIdentifier = 'test-client-type-password-public-valid';
        $ScopeIdentifiers = ['user.username.read', 'user.email_address.read'];
        $state = '12345';
        $redirectUri = 'http://localhost/redirect_uri/';
        Yii::$app->user->setIdentity($user);
        $controller = new Controller('test', $module);
        $accessTokenAction = new Oauth2AuthorizeAction('test', $controller);

        Yii::$app->request->setQueryParams([
            'response_type' => 'code',
            'client_id' => $clientIdentifier,
            'state' => $state,
            'scope' => implode(' ', $ScopeIdentifiers),
            'redirect_uri' => $redirectUri,
            'code_challenge' => 'X9lWKyT6K_fuuWtp8Ij5HG9urPGas7v0Z2RGwisf49c',
            'code_challenge_method' => 'S256',
        ]);

        $clientAuthorizationRequest = new Oauth2ClientAuthorizationRequest([
            'module' => $module,
            'clientIdentifier' => $clientIdentifier,
            'userIdentity' => $user,
            'requestedScopeIdentifiers' => $ScopeIdentifiers,
            'grantType' => Oauth2Module::GRANT_TYPE_AUTH_CODE,
            'authorizeUrl' => 'http://localhost/oauth2/authorize-client',
            'redirectUri' => $redirectUri,
            'state' => $state,
        ]);
        $module->setClientAuthReqSession($clientAuthorizationRequest);

        $response = $accessTokenAction->run($clientAuthorizationRequest->getRequestId());

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(HttpCode::FOUND, $response->statusCode);
        $this->assertStringStartsWith('http://localhost/oauth2/authorize-client', $response->headers->get('location'));
        $this->assertStringContainsString('clientAuthorizationRequestId=', $response->headers->get('location'));
    }

    public function testRunAuthenticatedUserCompletedClientAuthorizationRequestAuthorizationDenied()
    {
        $this->mockWebApplication();
        $module = Oauth2Module::getInstance();

        $user = TestUserModel::findOne(124); // 'test.user2'.
        $clientIdentifier = 'test-client-type-password-public-valid';
        $ScopeIdentifiers = ['user.username.read', 'user.email_address.read'];
        $state = '12345';
        $redirectUri = 'http://localhost/redirect_uri/';
        Yii::$app->user->setIdentity($user);
        $controller = new Controller('test', $module);
        $accessTokenAction = new Oauth2AuthorizeAction('test', $controller);

        Yii::$app->request->setQueryParams([
            'response_type' => 'code',
            'client_id' => $clientIdentifier,
            'state' => $state,
            'scope' => implode(' ', $ScopeIdentifiers),
            'redirect_uri' => $redirectUri,
            'code_challenge' => 'X9lWKyT6K_fuuWtp8Ij5HG9urPGas7v0Z2RGwisf49c',
            'code_challenge_method' => 'S256',
        ]);

        $clientAuthorizationRequest = new Oauth2ClientAuthorizationRequest([
            'module' => $module,
            'clientIdentifier' => $clientIdentifier,
            'userIdentity' => $user,
            'requestedScopeIdentifiers' => $ScopeIdentifiers,
            'grantType' => Oauth2Module::GRANT_TYPE_AUTH_CODE,
            'authorizeUrl' => 'http://localhost/oauth2/authorize-client',
            'redirectUri' => $redirectUri,
            'completed' => true,
            'state' => $state,
        ]);
        $module->setClientAuthReqSession($clientAuthorizationRequest);

        $response = $accessTokenAction->run($clientAuthorizationRequest->getRequestId());

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(HttpCode::FOUND, $response->statusCode);
        $this->assertEquals(
            'http://localhost/redirect_uri/?state=12345&error=access_denied',
            $response->headers->get('location')
        );
    }

    public function testRunAuthenticatedUserCompletedClientAuthorizationRequestAuthorizationApproved()
    {
        $this->mockWebApplication();
        $module = Oauth2Module::getInstance();

        $user = TestUserModel::findOne(124); // 'test.user2'.
        $clientIdentifier = 'test-client-type-password-public-valid';
        $ScopeIdentifiers = ['user.username.read', 'user.email_address.read'];
        $state = '12345';
        $redirectUri = 'http://localhost/redirect_uri/';
        Yii::$app->user->setIdentity($user);
        $controller = new Controller('test', $module);
        $accessTokenAction = new Oauth2AuthorizeAction('test', $controller);

        Yii::$app->request->setQueryParams([
            'response_type' => 'code',
            'client_id' => $clientIdentifier,
            'state' => $state,
            'scope' => implode(' ', $ScopeIdentifiers),
            'redirect_uri' => $redirectUri,
            'code_challenge' => 'X9lWKyT6K_fuuWtp8Ij5HG9urPGas7v0Z2RGwisf49c',
            'code_challenge_method' => 'S256',
        ]);

        // phpcs:disable Generic.Files.LineLength.TooLong -- readability actually better on single line
        $clientAuthorizationRequest = new Oauth2ClientAuthorizationRequest([
            'module' => $module,
            'clientIdentifier' => $clientIdentifier,
            'userIdentity' => $user,
            'requestedScopeIdentifiers' => $ScopeIdentifiers,
            'grantType' => Oauth2Module::GRANT_TYPE_AUTH_CODE,
            'authorizeUrl' => 'http://localhost/oauth2/authorize-client',
            'redirectUri' => $redirectUri,
            'authorizationStatus' => Oauth2ClientAuthorizationRequest::AUTHORIZATION_APPROVED, // Note, should be set before `completed`.
            'completed' => true,
            'state' => $state,
        ]);
        // phpcs:enable Generic.Files.LineLength.TooLong
        $module->setClientAuthReqSession($clientAuthorizationRequest);

        $response = $accessTokenAction->run($clientAuthorizationRequest->getRequestId());

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(HttpCode::FOUND, $response->statusCode);
        $this->assertStringStartsWith('http://localhost/redirect_uri/?', $response->headers->get('location'));
        $this->assertStringContainsString('code=', $response->headers->get('location'));
        $this->assertStringContainsString('state=12345', $response->headers->get('location'));
    }

    public function testRunAuthenticatedUserPreApproved()
    {
        $this->mockWebApplication();
        $module = Oauth2Module::getInstance();

        $user = TestUserModel::findOne(124); // 'test.user2'.
        $clientIdentifier = 'test-client-type-password-public-valid';
        $ScopeIdentifiers = ['user.username.read', 'user.email_address.read'];

        // Using https to allow pre-approved client autorization.
        $redirectUri = 'https://localhost/redirect_uri/';
        $client = Oauth2Client::findOne(['identifier' => $clientIdentifier]);
        $client->setRedirectUri($redirectUri);
        $client->persist();

        Yii::$app->user->setIdentity($user);
        $controller = new Controller('test', $module);
        $accessTokenAction = new Oauth2AuthorizeAction('test', $controller);

        Yii::$app->request->setQueryParams([
            'response_type' => 'code',
            'client_id' => $clientIdentifier,
            'state' => '12345',
            'scope' => implode(' ', $ScopeIdentifiers),
            'redirect_uri' => $redirectUri,
            'code_challenge' => 'X9lWKyT6K_fuuWtp8Ij5HG9urPGas7v0Z2RGwisf49c',
            'code_challenge_method' => 'S256',
        ]);

        $response = $accessTokenAction->run();

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(HttpCode::FOUND, $response->statusCode);
        $this->assertStringStartsWith('https://localhost/redirect_uri/?', $response->headers->get('location'));
        $this->assertStringContainsString('code=', $response->headers->get('location'));
        $this->assertStringContainsString('state=12345', $response->headers->get('location'));
    }

    public function testRunAuthenticatedUserNonExistingClientAuthorizationRequestId()
    {
        $this->mockWebApplication();
        $module = Oauth2Module::getInstance();

        Yii::$app->user->setIdentity(TestUserModel::findOne(123)); // 'test.user'.
        $controller = new Controller('test', $module);
        $accessTokenAction = new Oauth2AuthorizeAction('test', $controller);

        Yii::$app->request->setQueryParams([
            'response_type' => 'code',
            'client_id' => 'test-client-type-password-public-valid',
            'state' => '12345',
            'scope' => 'user.username.read user.email_address.read',
            'redirect_uri' => 'http://localhost/redirect_uri/',
            'code_challenge' => 'X9lWKyT6K_fuuWtp8Ij5HG9urPGas7v0Z2RGwisf49c',
            'code_challenge_method' => 'S256',
        ]);

        $response = $accessTokenAction->run('does not exist');

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(HttpCode::FOUND, $response->statusCode);
        $this->assertStringStartsWith('http://localhost/oauth2/authorize-client', $response->headers->get('location'));
        $this->assertStringContainsString('clientAuthorizationRequestId=', $response->headers->get('location'));
    }

    public function testRunInvalidScope()
    {
        $this->mockWebApplication([
            'modules' => [
                'oauth2' => [
                    'exceptionOnInvalidScope' => true,
                ],
            ],
        ]);
        $module = Oauth2Module::getInstance();

        $controller = new Controller('test', $module);
        $accessTokenAction = new Oauth2AuthorizeAction('test', $controller);

        Yii::$app->request->setQueryParams([
            'response_type' => 'code',
            'client_id' => 'test-client-type-password-public-valid',
            'state' => '12345',
            'scope' => 'defined-but-not-assigned',
            'redirect_uri' => 'http://localhost/redirect_uri/',
            'code_challenge' => 'X9lWKyT6K_fuuWtp8Ij5HG9urPGas7v0Z2RGwisf49c',
            'code_challenge_method' => 'S256',
        ]);

        $response = $accessTokenAction->run();

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(HttpCode::FOUND, $response->statusCode);
        $this->assertEquals(
            'http://localhost/redirect_uri/?error=scope_not_allowed_for_client',
            $response->headers->get('location')
        );
    }

    public function testRunNonExistingScope()
    {
        $this->mockWebApplication();
        $module = Oauth2Module::getInstance();

        $controller = new Controller('test', $module);
        $accessTokenAction = new Oauth2AuthorizeAction('test', $controller);

        Yii::$app->request->setQueryParams([
            'response_type' => 'code',
            'client_id' => 'test-client-type-password-public-valid',
            'state' => '12345',
            'scope' => 'does-not-exist',
            'redirect_uri' => 'http://localhost/redirect_uri/',
            'code_challenge' => 'X9lWKyT6K_fuuWtp8Ij5HG9urPGas7v0Z2RGwisf49c',
            'code_challenge_method' => 'S256',
        ]);

        $response = $accessTokenAction->run();

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(HttpCode::FOUND, $response->statusCode);
        $this->assertEquals('http://localhost/redirect_uri/?error=invalid_scope', $response->headers->get('location'));
    }

    public function testRunOAuthServerException()
    {
        $this->mockWebApplication();
        $config = $this->getMockWebAppConfig();
        $moduleConfig = $config['modules']['oauth2'];
        unset($moduleConfig['class']);
        $module = new class ('test', Yii::$app, $moduleConfig) extends Oauth2Module {
            public function getAuthorizationServer()
            {
                throw OAuthServerException::invalidCredentials();
            }
        };

        $controller = new Controller('test', $module);
        $accessTokenAction = new Oauth2AuthorizeAction('test', $controller);

        $response = $accessTokenAction->run();
        $this->assertEquals(HttpCode::BAD_REQUEST, $response->statusCode);
        $this->assertEquals('invalid_grant', $response->data['error']);
        $this->assertEquals('The user credentials were incorrect.', $response->data['error_description']);
    }

    public function testRunGeneralException()
    {
        $this->mockWebApplication();
        $config = $this->getMockWebAppConfig();
        $moduleConfig = $config['modules']['oauth2'];
        unset($moduleConfig['class']);
        $module = new class ('test', Yii::$app, $moduleConfig) extends Oauth2Module {
            public function getAuthorizationServer()
            {
                throw new InvalidCallException();
            }
        };

        $controller = new Controller('test', $module);
        $accessTokenAction = new Oauth2AuthorizeAction('test', $controller);

        $response = $accessTokenAction->run();
        $this->assertEquals(HttpCode::INTERNAL_SERVER_ERROR, $response->statusCode);
        $this->assertEquals('Exception', $response->data['error']);
    }

    public function testUserNotOauth2OidcUserComponentInterface()
    {
        $this->mockWebApplication([
            'components' => [
                'user' => [
                    'class' => UserComponent::class
                ],
            ],
        ]);

        $module = Oauth2Module::getInstance();

        $controller = new Controller('test', $module);
        $accessTokenAction = new Oauth2AuthorizeAction('test', $controller);

        $response = $accessTokenAction->run();
        $this->assertEquals(HttpCode::INTERNAL_SERVER_ERROR, $response->statusCode);
        $this->assertEquals('Invalid Configuration', $response->data['error']);
        $this->assertStringContainsString(
            'OpenId Connect is enabled but user component does not implement '
                . Oauth2OidcUserComponentInterface::class,
            $response->data['error_description']
        );
    }

    public function testInvalidClientAuthReqSessionState()
    {
        $this->mockWebApplication();
        $module = Oauth2Module::getInstance();

        Yii::$app->user->setIdentity(TestUserModel::findOne(123)); // 'test.user'.

        $controller = new Controller('test', $module);
        $accessTokenAction = new Oauth2AuthorizeAction('test', $controller);

        Yii::$app->request->setQueryParams([
            'response_type' => 'code',
            'client_id' => 'test-client-type-auth-code-valid',
            'state' => '12345',
            'redirect_uri' => 'http://localhost/redirect_uri/',
            'code_challenge' => 'X9lWKyT6K_fuuWtp8Ij5HG9urPGas7v0Z2RGwisf49c',
            'code_challenge_method' => 'S256',
        ]);

        $clientAuthorizationRequest = new Oauth2ClientAuthorizationRequest([
            'state' => 'different-state',
        ]);

        $module->setClientAuthReqSession($clientAuthorizationRequest);

        $response = $accessTokenAction->run($clientAuthorizationRequest->getRequestId());
        $this->assertEquals(HttpCode::UNAUTHORIZED, $response->statusCode);
        $this->assertEquals('Unauthorized', $response->data['error']);
        $this->assertStringContainsString(
            'Invalid state.',
            $response->data['error_description']
        );
    }

    public function testPromptNoneForGuest()
    {
        $this->mockWebApplication();
        $module = Oauth2Module::getInstance();

        $controller = new Controller('test', $module);
        $accessTokenAction = new Oauth2AuthorizeAction('test', $controller);

        Yii::$app->request->setQueryParams([
            'response_type' => 'code',
            'client_id' => 'test-client-type-auth-code-open-id-connect',
            'state' => '12345',
            'scope' => 'openid',
            'redirect_uri' => 'http://localhost/redirect_uri/',
            'code_challenge' => 'X9lWKyT6K_fuuWtp8Ij5HG9urPGas7v0Z2RGwisf49c',
            'code_challenge_method' => 'S256',
            'prompt' => 'none'
        ]);

        $response = $accessTokenAction->run();

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(HttpCode::FOUND, $response->statusCode);
        $this->assertEquals('http://localhost/redirect_uri/?error=login_required', $response->headers->get('location'));
    }

    /**
     * @dataProvider promptLoginForAuthenticatedUserProvider
     */
    public function testPromptLoginForAuthenticatedUser($prompt, $maxAge, $expectLoginPrompt)
    {
        $mockUserComponent = $this->getMockBuilder(TestUserComponent::class)
            ->onlyMethods([
                'reauthenticationRequired',
            ])
            ->setConstructorArgs([
                'config' => [
                    'identityClass' => TestUserModelOidc::class,
                ],
            ])
            ->getMock();

        $mockUserComponentClass = get_class($mockUserComponent);

        $this->mockWebApplication([
            'components' => [
                'user' => [
                    'class' => $mockUserComponentClass,
                ],
            ],
        ]);

        $module = Oauth2Module::getInstance();

        Yii::$app->user->setIdentity(TestUserModelOidc::findOne(123)); // 'test.user'.

        $controller = new Controller('test', $module);
        $accessTokenAction = new Oauth2AuthorizeAction('test', $controller);

        Yii::$app->request->setQueryParams([
            'response_type' => 'code',
            'client_id' => 'test-client-type-auth-code-open-id-connect',
            'state' => '12345',
            'scope' => 'openid',
            'redirect_uri' => 'http://localhost/redirect_uri/',
            'code_challenge' => 'X9lWKyT6K_fuuWtp8Ij5HG9urPGas7v0Z2RGwisf49c',
            'code_challenge_method' => 'S256',
            'prompt' => $prompt,
            'max_age' => $maxAge,
        ]);

        $response = new Response([
            'statusCode' => 302,
        ]);
        $response->headers->add('location', 'http://localhost/site/login?reauthenticate');
        Yii::$app->user->expects($this->once())
            ->method('reauthenticationRequired')
            ->willReturn($response);

        $response = $accessTokenAction->run();

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(HttpCode::FOUND, $response->statusCode);
        if ($expectLoginPrompt) {
            $this->assertEquals('http://localhost/site/login?reauthenticate', $response->headers->get('location'));
        } else {
            $this->assertStringStartsWith(
                'http://localhost/oauth2/authorize-client?clientAuthorizationRequestId=',
                $response->headers->get('location')
            );
        }
    }

    /**
     * @return array[]
     * @see testPromptLoginForAuthenticatedUser()
     */
    public function promptLoginForAuthenticatedUserProvider()
    {
        return [
            [
                'prompt' => 'login',
                'maxAge' => null,
                'expectLoginPrompt' => true,
            ],
            [
                'prompt' => null,
                'maxAge' => 0,
                'expectLoginPrompt' => true,
            ],
            [
                'prompt' => null,
                'maxAge' => 3000, // 'test.user'. latest_authenticated_at is set to creation time - 3600.
                'expectLoginPrompt' => true,
            ],
            [
                'prompt' => null,
                'maxAge' => 4000, // 'test.user'. latest_authenticated_at is set to creation time - 3600.
                'expectLoginPrompt' => false,
            ],
            [
                'prompt' => null,
                'maxAge' => '', // expect empty string to be threaded as `null`.
                'expectLoginPrompt' => false,
            ],
        ];
    }

    /**
     * @param array $moduleConfig
     * @param array $clientConfig
     * @dataProvider promptNoneWithForcedUserAccountSelectionProvider
     */
    public function testPromptNoneWithForcedUserAccountSelection($moduleConfig, $clientConfig)
    {
        $clientIdentifier = 'test-client-type-auth-code-open-id-connect';

        if (!empty($clientConfig)) {
            Oauth2Client::updateAll($clientConfig, ['identifier' => $clientIdentifier]);
        }
        $this->mockWebApplication([
            'modules' => [
                'oauth2' => $moduleConfig,
            ],
        ]);

        $module = Oauth2Module::getInstance();

        Yii::$app->user->setIdentity(TestUserModel::findOne(123)); // 'test.user'.

        $controller = new Controller('test', $module);
        $accessTokenAction = new Oauth2AuthorizeAction('test', $controller);

        Yii::$app->request->setQueryParams([
            'response_type' => 'code',
            'client_id' => 'test-client-type-auth-code-open-id-connect',
            'state' => '12345',
            'scope' => 'openid',
            'redirect_uri' => 'http://localhost/redirect_uri/',
            'code_challenge' => 'X9lWKyT6K_fuuWtp8Ij5HG9urPGas7v0Z2RGwisf49c',
            'code_challenge_method' => 'S256',
            'prompt' => 'none'
        ]);

        $response = $accessTokenAction->run();

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(HttpCode::FOUND, $response->statusCode);
        $this->assertEquals(
            'http://localhost/redirect_uri/?error=account_selection_required',
            $response->headers->get('location')
        );
    }

    /**
     * @see testPromptNoneWithForcedUserAccountSelection()
     * @return array[]
     */
    public function promptNoneWithForcedUserAccountSelectionProvider()
    {
        return [
            [
                [
                    'defaultUserAccountSelection' => Oauth2Module::USER_ACCOUNT_SELECTION_ALWAYS,
                ],
                [],
            ],
            [
                [],
                [
                    'user_account_selection' => Oauth2Module::USER_ACCOUNT_SELECTION_ALWAYS,
                ],
            ],
        ];
    }

    public function testOidcAccountSelectionRequired()
    {
        $mockUserComponent = $this->getMockBuilder(TestUserComponent::class)
            ->onlyMethods([
                'accountSelectionRequired',
            ])
            ->setConstructorArgs([
                'config' => [
                    'identityClass' => TestUserModelOidc::class,
                ],
            ])
            ->getMock();

        $mockUserComponentClass = get_class($mockUserComponent);

        $this->mockWebApplication([
            'modules' => [
                'oauth2' => [
                    'defaultUserAccountSelection' => Oauth2Module::USER_ACCOUNT_SELECTION_UPON_CLIENT_REQUEST
                ],
            ],
            'components' => [
                'user' => [
                    'class' => $mockUserComponentClass,
                ],
            ],
        ]);

        $module = Oauth2Module::getInstance();

        Yii::$app->user->setIdentity(TestUserModel::findOne(123)); // 'test.user'.

        $controller = new Controller('test', $module);
        $accessTokenAction = new Oauth2AuthorizeAction('test', $controller);

        Yii::$app->request->setQueryParams([
            'response_type' => 'code',
            'client_id' => 'test-client-type-auth-code-open-id-connect',
            'state' => '12345',
            'scope' => 'openid',
            'redirect_uri' => 'http://localhost/redirect_uri/',
            'code_challenge' => 'X9lWKyT6K_fuuWtp8Ij5HG9urPGas7v0Z2RGwisf49c',
            'code_challenge_method' => 'S256',
            'prompt' => 'select_account'
        ]);

        $response = new Response([
            'statusCode' => 302,
        ]);
        $response->headers->add('location', 'http://localhost/site/account-selection');
        Yii::$app->user->expects($this->once())
            ->method('accountSelectionRequired')
            ->willReturn($response);

        $response = $accessTokenAction->run();

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(HttpCode::FOUND, $response->statusCode);
        $this->assertEquals('http://localhost/site/account-selection', $response->headers->get('location'));
    }

    public function testPromptNoneWhenCLientAuthenticationIsRequired()
    {
        $this->mockWebApplication();

        $module = Oauth2Module::getInstance();

        Yii::$app->user->setIdentity(TestUserModel::findOne(123)); // 'test.user'.

        $controller = new Controller('test', $module);
        $accessTokenAction = new Oauth2AuthorizeAction('test', $controller);

        Yii::$app->request->setQueryParams([
            'response_type' => 'code',
            'client_id' => 'test-client-type-auth-code-open-id-connect',
            'state' => '12345',
            'scope' => 'openid',
            'redirect_uri' => 'http://localhost/redirect_uri/',
            'code_challenge' => 'X9lWKyT6K_fuuWtp8Ij5HG9urPGas7v0Z2RGwisf49c',
            'code_challenge_method' => 'S256',
            'prompt' => 'none',
        ]);

        $response = $accessTokenAction->run();

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(HttpCode::FOUND, $response->statusCode);
        $this->assertEquals(
            'http://localhost/redirect_uri/?error=consent_required',
            $response->headers->get('location')
        );
    }

    public function testOidcRequestParamNotSupported()
    {
        $this->mockWebApplication();
        $module = Oauth2Module::getInstance();
        $controller = new Controller('test', $module);
        $accessTokenAction = new Oauth2AuthorizeAction('test', $controller);

        Yii::$app->request->setQueryParams([
            'request' => [],
        ]);

        $response = $accessTokenAction->run();
        $this->assertEquals(HttpCode::BAD_REQUEST, $response->statusCode);
        $this->assertEquals('request_not_supported', $response->data['error']);
        $this->assertEquals(
            'The use of the "request" parameter is not supported Try to send the request as query parameters.',
            $response->data['error_description']
        );
    }

    public function testOidcRequestUriParamNotSupported()
    {
        $this->mockWebApplication();
        $module = Oauth2Module::getInstance();
        $controller = new Controller('test', $module);
        $accessTokenAction = new Oauth2AuthorizeAction('test', $controller);

        Yii::$app->request->setQueryParams([
            'request_uri' => '',
        ]);

        $response = $accessTokenAction->run();
        $this->assertEquals(HttpCode::BAD_REQUEST, $response->statusCode);
        $this->assertEquals('request_uri_not_supported', $response->data['error']);
        $this->assertEquals(
            'The use of the "request_uri" parameter is not supported Try to send the request as query parameters.',
            $response->data['error_description']
        );
    }

    public function testPromptNoneInvalid()
    {
        $this->mockWebApplication();
        $module = Oauth2Module::getInstance();
        $controller = new Controller('test', $module);
        $accessTokenAction = new Oauth2AuthorizeAction('test', $controller);

        Yii::$app->request->setQueryParams([
            'response_type' => 'code',
            'client_id' => 'test-client-type-auth-code-open-id-connect',
            'state' => '12345',
            'scope' => 'openid',
            'redirect_uri' => 'http://localhost/redirect_uri/',
            'code_challenge' => 'X9lWKyT6K_fuuWtp8Ij5HG9urPGas7v0Z2RGwisf49c',
            'code_challenge_method' => 'S256',
            'prompt' => 'none login',
        ]);

        $response = $accessTokenAction->run();
        $this->assertEquals(HttpCode::BAD_REQUEST, $response->statusCode);
        $this->assertEquals('Bad Request', $response->data['error']);
        $this->assertStringContainsString(
            'When the "prompt" parameter contains "none" other values are not allowed.',
            $response->data['error_description']
        );
    }

    public function testOidcOfflineAccessScopeWithoutConsentPrompt()
    {
        $this->mockWebApplication();
        $module = Oauth2Module::getInstance();
        $controller = new Controller('test', $module);
        $accessTokenAction = new Oauth2AuthorizeAction('test', $controller);

        Yii::$app->request->setQueryParams([
            'response_type' => 'code',
            'client_id' => 'test-client-type-auth-code-open-id-connect',
            'state' => '12345',
            'scope' => 'openid offline_access',
            'redirect_uri' => 'http://localhost/redirect_uri/',
            'code_challenge' => 'X9lWKyT6K_fuuWtp8Ij5HG9urPGas7v0Z2RGwisf49c',
            'code_challenge_method' => 'S256',
        ]);

        Yii::$app->user->setIdentity(TestUserModel::findOne(123)); // 'test.user'.

        $response = $accessTokenAction->run();
        $this->assertEquals(HttpCode::FOUND, $response->statusCode);
        $location = $response->headers->get('location');

        $locationParts = parse_url($location);
        $this->assertArrayHasKey('query', $locationParts);
        parse_str($locationParts['query'], $queryParts);
        $this->assertArrayHasKey('clientAuthorizationRequestId', $queryParts);
        $clientAuthorizationRequestId = $queryParts['clientAuthorizationRequestId'];

        $clientAuthReqSession =  $module->getClientAuthReqSession($clientAuthorizationRequestId);
        $scopeIdentifiers = $clientAuthReqSession->getRequestedScopeIdentifiers();

        $this->assertEquals(['openid'], $scopeIdentifiers); // The 'offline_access' scope should be ignored.
    }
}
