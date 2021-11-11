<?php

namespace Yii2Oauth2ServerTests\unit\models\_traits;

use DateTimeImmutable;
use Yii2Oauth2ServerTests\unit\models\_traits\_base\Oauth2BaseModelTestTrait;
use Yii2Oauth2ServerTests\unit\models\_traits\_base\Oauth2BasePhpUnitTestCaseTrait;

trait GetSetExpiryDateTimeTestTrait
{
    use Oauth2BaseModelTestTrait;
    use Oauth2BasePhpUnitTestCaseTrait;

    public function testGetSetExpiryDateTime()
    {
        $model = $this->getMockModel();
        $dateTime = new DateTimeImmutable('now +1 hour');
        $this->assertNull($model->getExpiryDateTime());
        $model->setExpiryDateTime($dateTime);
        $this->assertEquals($dateTime->getTimestamp(), $model->getExpiryDateTime()->getTimestamp());
    }
}
