<?php

namespace Yii2Oauth2ServerTests\unit\components\openidconnect\server\responses;

use rhertogh\Yii2Oauth2Server\components\server\responses\Oauth2BearerTokenResponse;
use rhertogh\Yii2Oauth2Server\Oauth2Module;
use Yii2Oauth2ServerTests\unit\DatabaseTestCase;

/**
 * @covers \rhertogh\Yii2Oauth2Server\components\server\responses\Oauth2BearerTokenResponse
 */
class Oauth2OidcBearerTokenResponseTest extends DatabaseTestCase
{
    public function testGetModule()
    {
        $response = new Oauth2BearerTokenResponse(Oauth2Module::getInstance());
        $this->assertInstanceOf(Oauth2Module::class, $response->getModule());
    }
}
