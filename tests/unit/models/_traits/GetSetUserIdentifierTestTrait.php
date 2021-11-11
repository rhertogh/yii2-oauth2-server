<?php

namespace Yii2Oauth2ServerTests\unit\models\_traits;

use Yii2Oauth2ServerTests\unit\models\_traits\_base\Oauth2BaseModelTestTrait;
use Yii2Oauth2ServerTests\unit\models\_traits\_base\Oauth2BasePhpUnitTestCaseTrait;

trait GetSetUserIdentifierTestTrait
{
    use Oauth2BaseModelTestTrait;
    use Oauth2BasePhpUnitTestCaseTrait;

    public function testGetSetUserIdentifier()
    {
        $model = $this->getMockModel();
        $userId = 123;
        $this->assertNull($model->getUserIdentifier());
        $model->setUserIdentifier($userId);
        $this->assertEquals($userId, $model->getUserIdentifier());
    }
}
