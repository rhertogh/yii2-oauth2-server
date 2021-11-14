<?php

namespace Yii2Oauth2ServerTests\_helpers;

use yii\httpclient\Response;
use yii\httpclient\Transport;
use Yii2Oauth2ServerTests\ApiTester;

class ApiTesterTransport extends Transport
{
    public ApiTester $apiTester;

    /**
     * Use the ApiTester to send Yii HttpClient requests
     * @inheritDoc
     * @param \yii\httpclient\Request $request
     * @return Response
     * @throws \yii\base\InvalidConfigException
     * @since 1.0.0
     */
    public function send($request)
    {
        $request->beforeSend();

        $request->prepare();

        $params = $request->getData();
        if ($params === null) {
            $params = [];
        }

        foreach ($request->headers->getIterator() as $name => $value) {
            if (is_array($value) && count($value)) {
                $value = $value[0];
            }
            $this->apiTester->haveHttpHeader($name, $value);
        }

        $this->apiTester->send(
            strtoupper($request->getMethod()),
            $request->getFullUrl(),
            $params
        );

        $responseHeaders = array_merge(
            [
                'http-code' => $this->apiTester->grabStatusCode(),
            ],
            $this->apiTester->grabHttpHeaders(),
        );
        $responseContent = $this->apiTester->grabResponse();

        $response = $request->client->createResponse($responseContent, $responseHeaders);

        $request->afterSend($response);

        return $response;
    }
}
