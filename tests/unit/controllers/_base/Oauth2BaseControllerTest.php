<?php

namespace Yii2Oauth2ServerTests\unit\controllers\_base;

use rhertogh\Yii2Oauth2Server\controllers\web\Oauth2ConsentController;
use rhertogh\Yii2Oauth2Server\Oauth2Module;
use Yii;
use yii\filters\Cors;
use yii\filters\VerbFilter;
use yii\web\Controller;
use Yii2Oauth2ServerTests\unit\TestCase;

abstract class Oauth2BaseControllerTest extends TestCase
{
    /**
     * @return Controller
     */
    abstract protected function getMockController();

    /**
     * @return string[]
     */
    abstract protected function getExpectedActions();

    public function testActions()
    {
        $controller = $this->getMockController();
        $actions = $controller->actions();

        foreach ($this->getExpectedActions() as $expectedAction) {
            $this->assertArrayHasKey($expectedAction, $actions);
        }
    }
}
