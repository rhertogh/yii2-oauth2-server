<?php

namespace Yii2Oauth2ServerTests\unit\components\server;

use rhertogh\Yii2Oauth2Server\Oauth2Module;
use Yii2Oauth2ServerTests\unit\TestCase;

/**
 * @covers \rhertogh\Yii2Oauth2Server\components\server\Oauth2AuthorizationServer
 */
class Oauth2AuthorizationServerTest extends TestCase
{
    public function testGetEnabledGranTypes()
    {
        $this->mockConsoleApplication();
        $authorizationServer = Oauth2Module::getInstance()->getAuthorizationServer();
        $this->assertEquals(
            [
                'authorization_code',
                'client_credentials',
                'implicit',
                'password',
                'refresh_token',
                'personal_access_token',
            ],
            array_keys($authorizationServer->getEnabledGrantTypes())
        );
    }
}
