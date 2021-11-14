<?php

namespace Yii2Oauth2ServerTests\unit\models\_traits;

use rhertogh\Yii2Oauth2Server\interfaces\models\Oauth2ClientInterface;
use Yii2Oauth2ServerTests\unit\models\_traits\_base\Oauth2BaseModelTestTrait;
use Yii2Oauth2ServerTests\unit\models\_traits\_base\Oauth2BasePhpUnitTestCaseTrait;
use Yii2Oauth2ServerTests\unit\models\Oauth2AuthCodeTest;

trait GetSetRedirectUriTestTrait
{
    use Oauth2BaseModelTestTrait;
    use Oauth2BasePhpUnitTestCaseTrait;

    public function testGetSetRedirectUri()
    {
        $uri = 'https://my.test/uri';
        /** @var Oauth2ClientInterface|Oauth2AuthCodeTest $model */
        $model = $this->getMockModel();

        $this->assertNull($model->getRedirectUri());
        $model->setRedirectUri($uri);
        $this->assertEquals($uri, $model->getRedirectUri());
    }
}
