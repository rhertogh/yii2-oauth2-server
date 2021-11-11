<?php

namespace Yii2Oauth2ServerTests\unit\models\_traits;

use rhertogh\Yii2Oauth2Server\helpers\DiHelper;
use rhertogh\Yii2Oauth2Server\interfaces\models\base\Oauth2ActiveRecordIdInterface;
use Yii2Oauth2ServerTests\unit\models\_traits\_base\Oauth2BaseModelTestTrait;
use Yii2Oauth2ServerTests\unit\models\_traits\_base\Oauth2BasePhpUnitTestCaseTrait;

trait Oauth2IdTestTrait
{
    use Oauth2BaseModelTestTrait;
    use Oauth2BasePhpUnitTestCaseTrait;

    /**
     * @param int $id
     *
     * @dataProvider findByIdTestProvider
     */
    public function testFindById($id)
    {
        /** @var Oauth2ActiveRecordIdInterface $className */
        $className = DiHelper::getValidatedClassName($this->getModelInterface());
        $model = $className::findById($id);

        $this->assertInstanceOf($this->getModelInterface(), $model);
        $this->assertInstanceOf($className, $model);
    }
}
