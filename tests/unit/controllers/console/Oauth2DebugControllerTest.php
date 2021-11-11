<?php

namespace Yii2Oauth2ServerTests\unit\controllers\console;

use rhertogh\Yii2Oauth2Server\controllers\console\Oauth2DebugController;
use rhertogh\Yii2Oauth2Server\Oauth2Module;
use Yii2Oauth2ServerTests\unit\controllers\_base\Oauth2BaseControllerTest;

/**
 * @covers \rhertogh\Yii2Oauth2Server\controllers\console\Oauth2DebugController
 */
class Oauth2DebugControllerTest extends Oauth2BaseControllerTest
{
    protected function getMockController()
    {
        $this->mockConsoleApplication();
        return new Oauth2DebugController('debug', Oauth2Module::getInstance());
    }

    /**
     * @inheritDoc
     */
    protected function getExpectedActions()
    {
        return [
            'config',
        ];
    }
}
