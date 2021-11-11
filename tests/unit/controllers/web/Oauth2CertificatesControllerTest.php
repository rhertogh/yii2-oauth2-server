<?php

namespace Yii2Oauth2ServerTests\unit\controllers\web;

use rhertogh\Yii2Oauth2Server\controllers\web\Oauth2CertificatesController;
use rhertogh\Yii2Oauth2Server\controllers\web\Oauth2ConsentController;
use rhertogh\Yii2Oauth2Server\controllers\web\Oauth2ServerController;
use rhertogh\Yii2Oauth2Server\controllers\web\Oauth2WellKnownController;
use rhertogh\Yii2Oauth2Server\controllers\web\server\Oauth2AccessTokenAction;
use rhertogh\Yii2Oauth2Server\controllers\web\server\Oauth2AuthorizeAction;
use rhertogh\Yii2Oauth2Server\Oauth2Module;
use yii\filters\Cors;
use yii\filters\VerbFilter;
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
