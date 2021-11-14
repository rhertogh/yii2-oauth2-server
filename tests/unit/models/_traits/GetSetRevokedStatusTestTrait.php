<?php

namespace Yii2Oauth2ServerTests\unit\models\_traits;

use DateTimeImmutable;
use Yii2Oauth2ServerTests\unit\models\_traits\_base\Oauth2BaseModelTestTrait;
use Yii2Oauth2ServerTests\unit\models\_traits\_base\Oauth2BasePhpUnitTestCaseTrait;

trait GetSetRevokedStatusTestTrait
{
    use Oauth2BaseModelTestTrait;
    use Oauth2BasePhpUnitTestCaseTrait;

    public function testGetSetRevokedStatus()
    {
        $model = $this->getMockModel();
        $this->assertFalse($model->getRevokedStatus()); // Default value.
        $model->setRevokedStatus(true);
        $this->assertTrue($model->getRevokedStatus());
        $model->setRevokedStatus(false);
        $this->assertFalse($model->getRevokedStatus());
    }
}
