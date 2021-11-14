<?php

namespace Yii2Oauth2ServerTests\unit\controllers\web\_base;

use yii\filters\Cors;
use yii\filters\VerbFilter;
use Yii2Oauth2ServerTests\unit\controllers\_base\Oauth2BaseControllerTest;

abstract class Oauth2BaseWebControllerTest extends Oauth2BaseControllerTest
{
    public function testVerbsBehavior()
    {
        $controller = $this->getMockController();
        $controller->ensureBehaviors();
        $behaviors = $controller->getBehaviors();
        $actions = $controller->actions();
        array_walk($actions, fn(&$val) => $val = false);

        foreach ($behaviors as $behavior) {
            if ($behavior instanceof VerbFilter) {
                foreach ($behavior->actions as $action => $verbs) {
                    $actions[$action] = true;
                }
            }
        }

        foreach ($actions as $action => $hasVerbs) {
            $this->assertTrue($hasVerbs, "No verbs specified for action '$action'.");
        }
    }

    protected function hasCorsBehavior()
    {
        $controller = $this->getMockController();
        $controller->ensureBehaviors();
        $behaviors = $controller->getBehaviors();

        foreach ($behaviors as $behavior) {
            if ($behavior instanceof Cors) {
                return true;
            }
        }

        return false;
    }
}
