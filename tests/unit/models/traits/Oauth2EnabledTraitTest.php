<?php

namespace Yii2Oauth2ServerTests\unit\models\traits;

use rhertogh\Yii2Oauth2Server\models\traits\Oauth2EnabledTrait;
use Yii2Oauth2ServerTests\unit\TestCase;

/**
 * @covers \rhertogh\Yii2Oauth2Server\models\traits\Oauth2EnabledTrait
 */
class Oauth2EnabledTraitTest extends TestCase
{
    public function testSetEnabled()
    {
        $model = new class {
            use Oauth2EnabledTrait;

            public $enabled = false;
        };

        $model->setEnabled(true);
        $this->assertTrue($model->isEnabled());
    }
}
