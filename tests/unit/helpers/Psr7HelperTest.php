<?php

namespace Yii2Oauth2ServerTests\unit\helpers;

use GuzzleHttp\Psr7\Response as Psr7Response;
use rhertogh\Yii2Oauth2Server\helpers\Psr7Helper;
use yii\helpers\ArrayHelper;
use yii\helpers\UnsetArrayValue;
use yii\web\Request;
use Yii2Oauth2ServerTests\unit\TestCase;

/**
 * @covers \rhertogh\Yii2Oauth2Server\helpers\Psr7Helper
 */
class Psr7HelperTest extends TestCase
{
    public function testYiiToPsr7Request()
    {
        $method = 'POST';
        $server = 'test';
        $path = '/uri?abc=xyz';
        $headers = ['test-header' => ['test-header-content']];
        $rawBody = 'test-raw-body-content';
        $bodyParams = ['test-body-param' => 'test-body-param-content'];

        $_SERVER['SERVER_NAME'] = $server;
        $_SERVER['REQUEST_METHOD'] = $method; // Yii2 Request->$method is read only, using workaround
        $request = new Request([
            'url' => $path,
            'rawBody' => $rawBody,
            'bodyParams' => $bodyParams,
        ]);
        $request->headers->fromArray($headers);

        $psr7Request = Psr7Helper::yiiToPsr7Request($request);

        $this->assertEquals($method, $psr7Request->getMethod());
        $this->assertEquals('http://' . $server . $path, $psr7Request->getUri());
        $this->assertEquals(
            $headers,
            ArrayHelper::merge($psr7Request->getHeaders(), ['Host' => new UnsetArrayValue()])
        );
        $this->assertEquals($rawBody, $psr7Request->getBody());
        $this->assertEquals($bodyParams, $psr7Request->getParsedBody());
    }

    public function testPsr7ToYiiResponse()
    {
        $this->mockConsoleApplication();

        $status = 404;
        $headers = ['test-header' => ['test-header-content']];
        $rawBody = 'test-raw-body-content';

        $psr7Response = new Psr7Response($status, $headers, $rawBody);

        $response = Psr7Helper::psr7ToYiiResponse($psr7Response);

        $this->assertEquals($rawBody, $response->content);
        $this->assertEquals($status, $response->getStatusCode());
        $this->assertEquals($headers, $response->getHeaders()->toArray());
    }
}
