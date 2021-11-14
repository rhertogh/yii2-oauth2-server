<?php

namespace Yii2Oauth2ServerTests\unit\controllers\web\server\base;

use Codeception\Util\HttpCode;
use League\OAuth2\Server\Exception\OAuthServerException;
use rhertogh\Yii2Oauth2Server\components\authorization\Oauth2ClientAuthorizationRequest;
use rhertogh\Yii2Oauth2Server\controllers\web\Oauth2ConsentController;
use rhertogh\Yii2Oauth2Server\controllers\web\consent\Oauth2AuthorizeClientAction;
use rhertogh\Yii2Oauth2Server\controllers\web\server\base\Oauth2BaseServerAction;
use rhertogh\Yii2Oauth2Server\exceptions\Oauth2ServerHttpException;
use rhertogh\Yii2Oauth2Server\interfaces\components\authorization\Oauth2ClientAuthorizationRequestInterface;
use rhertogh\Yii2Oauth2Server\models\Oauth2Client;
use rhertogh\Yii2Oauth2Server\Oauth2Module;
use Yii;
use yii\base\Exception;
use yii\base\Module;
use yii\helpers\ReplaceArrayValue;
use yii\helpers\UnsetArrayValue;
use yii\web\Application;
use yii\web\Controller;
use yii\web\Response;
use Yii2Oauth2ServerTests\_helpers\TestUserModel;
use Yii2Oauth2ServerTests\unit\TestCase;

use function PHPUnit\Framework\assertInstanceOf;

/**
 * @covers \rhertogh\Yii2Oauth2Server\controllers\web\server\base\Oauth2BaseServerAction
 */
class Oauth2BaseServerActionTest extends TestCase
{
    public function testProcessOAuthServerExceptionWithRedirect()
    {
        $this->mockWebApplication();
        $controller = new Controller('test', Oauth2Module::getInstance());
        $baseServerAction = new class ('test', $controller) extends Oauth2BaseServerAction {
            public function processExceptionTest($e)
            {
                return $this->processException($e);
            }
        };

        $redirectUri = 'https://localhost/redirect_uri';
        $e = OAuthServerException::invalidScope('scope', $redirectUri);
        $response = $baseServerAction->processExceptionTest($e);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(HttpCode::FOUND, $response->statusCode);
        $this->assertEquals($redirectUri . '?error=invalid_scope', $response->headers->get('location'));
    }

    public function testProcessOAuthServerExceptionWithoutRedirect()
    {
        $this->mockWebApplication();
        $controller = new Controller('test', Oauth2Module::getInstance());
        $baseServerAction = new class ('test', $controller) extends Oauth2BaseServerAction{
            public function processExceptionTest($e)
            {
                return $this->processException($e);
            }
        };

        $e = OAuthServerException::unsupportedGrantType();

        $response = $baseServerAction->processExceptionTest($e);
        $this->assertEquals(HttpCode::BAD_REQUEST, $response->statusCode);
        $this->assertEquals('unsupported_grant_type', $response->data['error']);
        $this->assertEquals(
            'The authorization grant type is not supported by the authorization server.'
            . ' Check that all required parameters have been provided',
            $response->data['error_description']
        );
    }

    public function testProcessExceptionWithoutAppResponse()
    {
        $this->mockWebApplication();

        $controller = new Controller('test', Oauth2Module::getInstance());
        $baseServerAction = new class ('test', $controller) extends Oauth2BaseServerAction {
            public function processExceptionTest($e)
            {
                return $this->processException($e);
            }
        };

        // Clear request component.
        $appComponents = $this->getInaccessibleProperty(Yii::$app, '_components');
        unset($appComponents['response']);
        $this->setInaccessibleProperty(Yii::$app, '_components', $appComponents);
        $appDefinitions = $this->getInaccessibleProperty(Yii::$app, '_definitions');
        unset($appDefinitions['response']);
        $this->setInaccessibleProperty(Yii::$app, '_definitions', $appDefinitions);

        $this->assertFalse(Yii::$app->has('response'));

        $e = new Exception('test exception');
        $response = $baseServerAction->processExceptionTest($e);
        $this->assertInstanceOf(Response::class, $response);
    }

    /**
     * @dataProvider processExceptionDisplayConfidentialExceptionMessagesProvider
     */
    public function testProcessExceptionDisplayConfidentialExceptionMessages(
        $displayConfidentialExceptionMessages,
        $expectMessage
    ) {
        $this->mockWebApplication([
            'modules' => [
                'oauth2' => [
                    'displayConfidentialExceptionMessages' => $displayConfidentialExceptionMessages,
                ],
            ],
        ]);

        $controller = new Controller('test', Oauth2Module::getInstance());
        $baseServerAction = new class ('test', $controller) extends Oauth2BaseServerAction {
            public function processExceptionTest($e)
            {
                return $this->processException($e);
            }
        };

        $exceptionMessage = 'test exception';
        $e = new Exception($exceptionMessage);

        $response = $baseServerAction->processExceptionTest($e);
        $this->assertEquals(HttpCode::INTERNAL_SERVER_ERROR, $response->statusCode);
        $this->assertEquals('Exception', $response->data['error']);
        if ($expectMessage) {
            $this->assertStringContainsString($exceptionMessage, $response->data['error_description']);
        } else {
            $this->assertEquals('An internal server error occurred.', $response->data['error_description']);
        }
    }

    /**
     * @see testProcessExceptionDisplayConfidentialExceptionMessages()
     * @return array
     */
    public function processExceptionDisplayConfidentialExceptionMessagesProvider()
    {
        return [
            [
                true,
                true,
            ],
            [
                false,
                false,
            ],
            [
                null,
                YII_DEBUG,
            ],
        ];
    }
}
