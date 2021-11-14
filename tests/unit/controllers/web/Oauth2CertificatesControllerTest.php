<?php

namespace Yii2Oauth2ServerTests\unit\controllers\web;

use rhertogh\Yii2Oauth2Server\controllers\web\Oauth2CertificatesController;
use rhertogh\Yii2Oauth2Server\Oauth2Module;
use Yii2Oauth2ServerTests\unit\controllers\web\_base\Oauth2BaseWebControllerTest;

/**
 * @covers \rhertogh\Yii2Oauth2Server\controllers\web\Oauth2CertificatesController
 */
class Oauth2CertificatesControllerTest extends Oauth2BaseWebControllerTest
{
    protected function getMockController()
    {
        $this->mockWebApplication();
        return new Oauth2CertificatesController('server', Oauth2Module::getInstance());
    }

    /**
     * @inheritDoc
     */
    protected function getExpectedActions()
    {
        return [
            Oauth2CertificatesController::ACTION_NAME_JWKS,
        ];
    }

    public function testCorsBehavior()
    {
        $this->assertTrue($this->hasCorsBehavior(), 'Oauth2CertificatesController should have a CORS filter.');
    }
}
