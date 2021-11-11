<?php

namespace Yii2Oauth2ServerTests\unit\controllers\console;

use rhertogh\Yii2Oauth2Server\controllers\console\Oauth2MigrationsController;
use rhertogh\Yii2Oauth2Server\Oauth2Module;
use Yii2Oauth2ServerTests\unit\controllers\_base\Oauth2BaseControllerTest;

/**
 * @covers \rhertogh\Yii2Oauth2Server\controllers\console\Oauth2MigrationsController
 */
class Oauth2MigrationsControllerTest extends Oauth2BaseControllerTest
{
    protected function getMockController()
    {
        $this->mockConsoleApplication();
        return new Oauth2MigrationsController('migrations', Oauth2Module::getInstance());
    }

    /**
     * @inheritDoc
     */
    protected function getExpectedActions()
    {
        return [
            'generate',
        ];
    }

    public function testOptions()
    {
        $controller = $this->getMockController();

        $options = $controller->options('generate');
        $this->assertContains('force', $options);
    }

    public function testOptionsAliases()
    {
        $controller = $this->getMockController();

        $optionAliases = $controller->optionAliases();
        $this->assertContains('force', $optionAliases);
    }
}
