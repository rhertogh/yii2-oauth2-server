<?php

namespace Yii2Oauth2ServerTests\unit\components\server;

use Defuse\Crypto\Exception\EnvironmentIsBrokenException;
use rhertogh\Yii2Oauth2Server\components\encryption\Oauth2Encryptor;
use rhertogh\Yii2Oauth2Server\interfaces\components\factories\encryption\Oauth2EncryptionKeyFactoryInterface;
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
            ],
            array_keys($authorizationServer->getEnabledGrantTypes())
        );
    }
}
