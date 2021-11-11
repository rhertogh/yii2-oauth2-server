<?php

namespace Yii2Oauth2ServerTests\unit\models\_traits;

use rhertogh\Yii2Oauth2Server\helpers\DiHelper;
use rhertogh\Yii2Oauth2Server\interfaces\models\base\Oauth2ActiveRecordInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\base\Oauth2IdentifierInterface;
use Yii;
use Yii2Oauth2ServerTests\unit\models\_traits\_base\Oauth2BaseModelTestTrait;
use Yii2Oauth2ServerTests\unit\models\_traits\_base\Oauth2BasePhpUnitTestCaseTrait;

trait Oauth2IdentifierTestTrait
{
    use Oauth2BaseModelTestTrait;
    use Oauth2BasePhpUnitTestCaseTrait;

    /**
     * @return array[]
     */
    abstract public function findByIdentifierTestProvider();

    /**
     * @return array[]
     */
    abstract public function identifierExistsProvider();

    /**
     * @param string $identifier
     *
     * @dataProvider findByIdentifierTestProvider
     */
    public function testFindByIdentifier($identifier)
    {
        /** @var Oauth2IdentifierInterface $className */
        $className = DiHelper::getValidatedClassName($this->getModelInterface());
        $model = $className::findByIdentifier($identifier);

        $this->assertInstanceOf(Oauth2ActiveRecordInterface::class, $model);
        $this->assertEquals($identifier, $model->getIdentifier());
    }

    public function testGetSetIdentifier()
    {
        $identifier = 'my-test-identifier';

        /** @var Oauth2IdentifierInterface $model */
        $model = Yii::createObject($this->getModelInterface());
        $this->assertNull($model->getIdentifier());
        $model->setIdentifier($identifier);
        $this->assertEquals($identifier, $model->getIdentifier());
    }

    /**
     * @param $identifier
     * @param $exists
     *
     * @dataProvider identifierExistsProvider
     */
    public function testIdentifierExists($identifier, $exists)
    {
        /** @var Oauth2IdentifierInterface $model */
        $model = Yii::createObject($this->getModelInterface());
        $model->setIdentifier($identifier);
        $this->assertEquals($exists, $model->identifierExists());
    }
}
