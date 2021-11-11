<?php

namespace Yii2Oauth2ServerTests\unit\controllers\web\certificates;

use Jose\Component\Core\JWK;
use Jose\Component\Core\JWKSet;
use rhertogh\Yii2Oauth2Server\controllers\web\certificates\Oauth2JwksAction;
use rhertogh\Yii2Oauth2Server\controllers\web\Oauth2CertificatesController;
use rhertogh\Yii2Oauth2Server\Oauth2Module;
use Yii2Oauth2ServerTests\unit\TestCase;

/**
 * @covers \rhertogh\Yii2Oauth2Server\controllers\web\certificates\Oauth2JwksAction
 */
class Oauth2JwksActionTest extends TestCase
{
    protected function getMockController()
    {
        return new Oauth2CertificatesController('certificates', Oauth2Module::getInstance());
    }

    public function testRunOK()
    {
        $this->mockWebApplication();

        $actionAction = new Oauth2JwksAction('jwks', $this->getMockController());
        $response = $actionAction->run();

        $this->assertInstanceOf(JWKSet::class, $response);
        $this->assertEquals(1, $response->count());
        foreach ($response->all() as $jwk) {
            $this->assertInstanceOf(JWK::class, $jwk);
            $jwkSettings = $jwk->all();
            $this->assertNotEmpty($jwkSettings['kty']);
            $this->assertNotEmpty($jwkSettings['alg']);
            $this->assertNotEmpty($jwkSettings['use']);
            $this->assertNotEmpty($jwkSettings['n']);
            $this->assertNotEmpty($jwkSettings['e']);
        }
    }
}
