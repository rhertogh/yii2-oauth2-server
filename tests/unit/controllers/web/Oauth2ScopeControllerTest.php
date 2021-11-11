<?php

namespace Yii2Oauth2ServerTests\unit\controllers\web;

use rhertogh\Yii2Oauth2Server\controllers\web\Oauth2ConsentController;
use rhertogh\Yii2Oauth2Server\Oauth2Module;
use Yii2Oauth2ServerTests\unit\controllers\web\_base\Oauth2BaseWebControllerTest;

/**
 * @covers \rhertogh\Yii2Oauth2Server\controllers\web\Oauth2ConsentController
 */
class Oauth2ScopeControllerTest extends Oauth2BaseWebControllerTest
{
    protected function getMockController()
    {
        $this->mockWebApplication();
        return new Oauth2ConsentController('scopes', Oauth2Module::getInstance());
    }

    /**
     * @inheritDoc
     */
    protected function getExpectedActions()
    {
        return [
            Oauth2ConsentController::ACTION_NAME_AUTHORIZE_CLIENT,
        ];
    }
}
