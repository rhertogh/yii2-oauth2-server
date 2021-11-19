<?php

namespace rhertogh\Yii2Oauth2Server\helpers;

use GuzzleHttp\Psr7\Response as Psr7Response;
use GuzzleHttp\Psr7\ServerRequest;
use GuzzleHttp\Psr7\Utils as Psr7Utils;
use Psr\Http\Message\ServerRequestInterface;
use Yii;
use yii\web\Request;
use yii\web\Response;

class Psr7Helper
{
    /**
     * Converts a Yii2 request into a PSR 7 request.
     * @param Request $request
     * @return ServerRequestInterface
     * @since 1.0.0
     */
    public static function yiiToPsr7Request($request)
    {
        $serverRequest = Yii::createObject(ServerRequest::class, [
            $request->method,
            $request->absoluteUrl,
            $request->headers->toArray(),
            $request->rawBody
        ]);

        return $serverRequest
            ->withParsedBody($request->bodyParams)
            ->withQueryParams($request->getQueryParams());
    }

    /**
     * Converts a Yii2 Response into a PSR 7 Response.
     * @param Response $response
     * @return Psr7Response
     * @since 1.0.0
     */
    public static function yiiToPsr7Response($response)
    {
        /** @var Psr7Response $psr7Response */
        $psr7Response = Yii::createObject(Psr7Response::class);

        foreach ($response->headers as $name => $value) {
            $psr7Response = $psr7Response->withHeader($name, $value);
        }

        return $psr7Response
            ->withStatus($response->getStatusCode())
            ->withBody(Psr7Utils::streamFor($response->content));
    }

    /**
     * Converts a PSR 7 Response into a Yii2 Response.
     * @param Psr7Response $psr7Response
     * @return Response
     * @since 1.0.0
     */
    public static function psr7ToYiiResponse($psr7Response)
    {
        /** @var Response $response */
        $response = Yii::createObject([
            'class' => Response::class,
            'statusCode' => $psr7Response->getStatusCode(),
            'content' => (string)$psr7Response->getBody(),
        ]);

        $response->headers->fromArray(array_change_key_case($psr7Response->getHeaders(), CASE_LOWER));

        return $response;
    }
}
