<?php

namespace Yii2Oauth2ServerTests\unit\controllers\web;

use rhertogh\Yii2Oauth2Server\controllers\web\Oauth2OidcController;
use rhertogh\Yii2Oauth2Server\Oauth2Module;
use Yii2Oauth2ServerTests\unit\controllers\web\_base\Oauth2BaseWebControllerTest;

/**
 * @covers \rhertogh\Yii2Oauth2Server\controllers\web\Oauth2OidcController
 */
class Oauth2OidcControllerTest extends Oauth2BaseWebControllerTest
{
    protected function getMockController()
    {
        $this->mockWebApplication();
        return new Oauth2OidcController('oidc', Oauth2Module::getInstance());
    }

    /**
     * @inheritDoc
     */
    protected function getExpectedActions()
    {
        return [
            Oauth2OidcController::ACTION_NAME_USERINFO,
            Oauth2OidcController::ACTION_END_SESSION,
        ];
    }

    public function testCorsBehavior()
    {
        $this->assertTrue($this->hasCorsBehavior(), 'Oauth2OidcController should have a CORS filter.');
    }
}
