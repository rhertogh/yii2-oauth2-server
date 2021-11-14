<?php

namespace Yii2Oauth2ServerTests\unit\controllers\web\server;

use Codeception\Util\HttpCode;
use League\OAuth2\Server\Exception\OAuthServerException;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use rhertogh\Yii2Oauth2Server\controllers\web\server\Oauth2AccessTokenAction;
use rhertogh\Yii2Oauth2Server\exceptions\Oauth2ServerHttpException;
use rhertogh\Yii2Oauth2Server\Oauth2Module;
use Yii;
use yii\base\InvalidCallException;
use yii\web\Controller;
use yii\web\Response;
use Yii2Oauth2ServerTests\unit\TestCase;

/**
 * @covers \rhertogh\Yii2Oauth2Server\controllers\web\server\Oauth2AccessTokenAction
 */
class Oauth2AccessTokenActionTest extends TestCase
{
    public function testRunOK()
    {
        $this->mockWebApplication();
        $config = $this->getMockWebAppConfig();
        $moduleConfig = $config['modules']['oauth2'];
        unset($moduleConfig['class']);
        $module = new class ('test', Yii::$app, $moduleConfig) extends Oauth2Module {
            public function getAuthorizationServer()
            {
                return new class {
                    /**
                     * @param RequestInterface $psr7Request
                     * @param ResponseInterface $psr7Response
                     * @return mixed
                     */
                    public function respondToAccessTokenRequest($psr7Request, $psr7Response)
                    {
                        $psr7Response->getBody()->write('test.body');
                        return $psr7Response;
                    }
                };
            }
        };

        $controller = new Controller('test', $module);
        $accessTokenAction = new Oauth2AccessTokenAction('test', $controller);

        $response = $accessTokenAction->run();

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals('test.body', $response->content);
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
        $accessTokenAction = new Oauth2AccessTokenAction('test', $controller);

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
        $accessTokenAction = new Oauth2AccessTokenAction('test', $controller);

        $response = $accessTokenAction->run();
        $this->assertEquals(HttpCode::INTERNAL_SERVER_ERROR, $response->statusCode);
        $this->assertEquals('Exception', $response->data['error']);
    }
}
