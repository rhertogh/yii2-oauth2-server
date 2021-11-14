<?php

namespace Yii2Oauth2ServerTests;

use Psr\Http\Message\RequestInterface;
use yii\httpclient\Client;
use yii\httpclient\Request as HttpClientRequest;
use yii\httpclient\Response;

/**
 * Inherited Methods
 * @method void wantToTest($text)
 * @method void wantTo($text)
 * @method void execute($callable)
 * @method void expectTo($prediction)
 * @method void expect($prediction)
 * @method void amGoingTo($argumentation)
 * @method void am($role)
 * @method void lookForwardTo($achieveValue)
 * @method void comment($description)
 * @method void pause()
 *
 * @SuppressWarnings(PHPMD)
 */
class ApiTester extends \Codeception\Actor
{
    use _generated\ApiTesterActions;

    /**
     * Define custom actions here
     */

    /**
     * @param RequestInterface $request
     */
    public function sendPsr7Request($request)
    {
        parse_str($request->getBody()->getContents(), $bodyParams);

        $this->send(
            $request->getMethod(),
            (string)($request->getUri()),
            $bodyParams
        );
    }

    /**
     * @param HttpClientRequest $request
     * @return Response
     */
    public function sendYiiHttpClientRequest($request)
    {
    }
}
