<?php

namespace Yii2Oauth2ServerTests\unit\controllers\web\scopes;

use Codeception\Util\HttpCode;
use rhertogh\Yii2Oauth2Server\components\authorization\client\Oauth2ClientAuthorizationRequest;
use rhertogh\Yii2Oauth2Server\controllers\web\consent\Oauth2AuthorizeClientAction;
use rhertogh\Yii2Oauth2Server\controllers\web\Oauth2ConsentController;
use rhertogh\Yii2Oauth2Server\interfaces\controllers\web\Oauth2ConsentControllerInterface;
use rhertogh\Yii2Oauth2Server\Oauth2Module;
use Yii;
use yii\web\Response;
use Yii2Oauth2ServerTests\_helpers\TestUserModel;
use Yii2Oauth2ServerTests\unit\DatabaseTestCase;

/**
 * @covers \rhertogh\Yii2Oauth2Server\controllers\web\consent\Oauth2AuthorizeClientAction
 */
class Oauth2AuthorizeClientActionTest extends DatabaseTestCase
{
    protected function getMockController()
    {
        return new Oauth2ConsentController(
            Oauth2ConsentControllerInterface::CONTROLLER_NAME,
            Oauth2Module::getInstance()
        );
    }

    public function testInitInvalidClientAuthorizationView()
    {
        $this->mockWebApplication();
        $this->expectExceptionMessage('$clientAuthorizationView must be set.');
        new Oauth2AuthorizeClientAction('Oauth2AuthorizeClientActionTest', $this->getMockController());
    }

    public function testRunView()
    {
        $this->mockWebApplication();
        $user = TestUserModel::findOne(123);

        Yii::$app->user->setIdentity($user);
        $controller = $this->getMockController();
        $module = $controller->module;
        $authorizeClientAction = new Oauth2AuthorizeClientAction('Oauth2AuthorizeClientActionTest', $controller, [
            'clientAuthorizationView' => $module->clientAuthorizationView,
        ]);

        $clientAuthorizationRequest = new Oauth2ClientAuthorizationRequest([
            'userIdentity' => $user,
            'clientIdentifier' => 'test-client-type-auth-code-valid',
        ]);
        $module->setClientAuthReqSession($clientAuthorizationRequest);

        $output = $authorizeClientAction->run($clientAuthorizationRequest->getRequestId());
        $this->assertStringContainsString($clientAuthorizationRequest->getClient()->getName(), $output);
    }

    public function testRunPost()
    {
        $this->mockWebApplication();
        $user = TestUserModel::findOne(123);

        Yii::$app->user->setIdentity($user);
        $controller = $this->getMockController();
        $module = $controller->module;
        $authorizeClientAction = new Oauth2AuthorizeClientAction('Oauth2AuthorizeClientActionTest', $controller, [
            'clientAuthorizationView' => $module->clientAuthorizationView,
        ]);

        $clientAuthorizationRequest = new Oauth2ClientAuthorizationRequest([
            'userIdentity' => $user,
            'clientIdentifier' => 'test-client-type-auth-code-valid',
        ]);
        $module->setClientAuthReqSession($clientAuthorizationRequest);

        Yii::$app->request->setBodyParams([
            'Oauth2ClientAuthorizationRequest' => [
                'authorizationStatus' => Oauth2ClientAuthorizationRequest::AUTHORIZATION_APPROVED,
            ],
        ]);

        $response = $authorizeClientAction->run($clientAuthorizationRequest->getRequestId());
        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(HttpCode::FOUND, $response->statusCode);
    }

    public function testRunInvalidRequestId()
    {
        $this->mockWebApplication();
        $controller = $this->getMockController();
        $module = $controller->module;
        $authorizeClientAction = new Oauth2AuthorizeClientAction('Oauth2AuthorizeClientActionTest', $controller, [
            'clientAuthorizationView' => $module->clientAuthorizationView,
        ]);

        $this->expectExceptionMessage(
            'Unable to respond to client authorization request. Invalid clientAuthorizationRequestId.'
        );
        $authorizeClientAction->run('test');
    }
}
