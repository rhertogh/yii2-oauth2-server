<?php

namespace Yii2Oauth2ServerTests\unit\components\server\grants\_base;

use GuzzleHttp\Psr7\ServerRequest;
use League\OAuth2\Server\Exception\OAuthServerException;
use rhertogh\Yii2Oauth2Server\interfaces\components\server\grants\base\Oauth2GrantTypeInterface;
use rhertogh\Yii2Oauth2Server\models\Oauth2Client;
use rhertogh\Yii2Oauth2Server\Oauth2Module;
use Yii2Oauth2ServerTests\unit\DatabaseTestCase;

abstract class BaseOauth2GrantTest extends DatabaseTestCase
{
    /**
     * @param Oauth2Module $module
     * @return Oauth2GrantTypeInterface
     */
    abstract protected function getMockGrant($module);

    /**
     * @dataProvider validateRedirectUriProvider
     */
    public function testValidateRedirectUri($redirectUri, $redirectUris, $allowVariableRedirectUriQuery, $expectValid)
    {
        $this->mockWebApplication();

        $grant = $this->getMockGrant(Oauth2Module::getInstance());
        $client = new Oauth2Client([
            'redirect_uris' => $redirectUris,
            'allow_variable_redirect_uri_query' => $allowVariableRedirectUriQuery
        ]);
        $request = new ServerRequest('POST', 'https://localhost/test');

        if (!$expectValid) {
            $this->expectException(OAuthServerException::class);
        }
        $this->callInaccessibleMethod($grant, 'validateRedirectUri', [$redirectUri, $client, $request]);

        $this->assertTrue($expectValid);
    }

    public function validateRedirectUriProvider()
    {
        return [
            'no query params, not variable, expect not allowed' => [
                'https://localhost/redirect-uri-test',
                ['https://localhost/redirect-uri'],
                false,
                false,
            ],

            'no query params, not variable, expect allowed' => [
                'https://localhost/redirect-uri',
                ['https://localhost/redirect-uri'],
                false,
                true,
            ],

            'no query params, variable, expect allowed' => [
                'https://localhost/redirect-uri',
                ['https://localhost/redirect-uri'],
                true,
                true,
            ],

            'no query params, variable, expect not allowed' => [
                'https://localhost/redirect-uri-test',
                ['https://localhost/redirect-uri'],
                true,
                false,
            ],

            'with both query params, not variable, expect allowed' => [
                'https://localhost/redirect-uri?test=1',
                ['https://localhost/redirect-uri?test=1'],
                false,
                true,
            ],

            'with both query params, not variable, expect not allowed' => [
                'https://localhost/redirect-uri?test=1',
                ['https://localhost/redirect-uri?test=2'],
                false,
                false,
            ],

            'with requested query params, not variable, expect not allowed' => [
                'https://localhost/redirect-uri?test=1',
                ['https://localhost/redirect-uri'],
                false,
                false,
            ],

            'with requested query params, variable, expect allowed' => [
                'https://localhost/redirect-uri?test=1',
                ['https://localhost/redirect-uri'],
                true,
                true,
            ],

            'with defined query params, not variable, expect not allowed' => [
                'https://localhost/redirect-uri',
                ['https://localhost/redirect-uri?test=1'],
                false,
                false,
            ],

            'with defined query params, variable, expect not allowed' => [
                'https://localhost/redirect-uri',
                ['https://localhost/redirect-uri?test=1'],
                true,
                false,
            ],
        ];
    }
}
