<?php

namespace Yii2Oauth2ServerTests\unit\models\base\_base;

use rhertogh\Yii2Oauth2Server\models\base\Oauth2BaseActiveRecord;
use yii\db\ActiveQuery;
use yii\db\ActiveQueryInterface;
use Yii2Oauth2ServerTests\unit\TestCase;

abstract class BaseOauth2BaseModelsTest extends TestCase
{
    /**
     * @return string
     */
    abstract protected function getBaseModelClass();

    /**
     * @return string
     */
    abstract protected function getQueryClass();

    /**
     * @return array[]
     */
    abstract public function attributeLabelsProvider();

    /**
     * @return array[]
     */
    abstract public function relationsProvider();

    public function testFind()
    {
        $class = $this->getBaseClassWrapper();
        $this->assertInstanceOf($this->getQueryClass(), $class::find());
    }

    public function testRules()
    {
        $class = $this->getBaseClassWrapper();
        $this->assertIsArray((new $class())->rules());
    }

    /**
     * @param string[] $attributeLabels
     *
     * @dataProvider attributeLabelsProvider
     */
    public function testAttributeLabels($attributeLabels)
    {
        $class = $this->getBaseClassWrapper();
        /** @var Oauth2BaseActiveRecord $baseModel */
        $baseModel = new $class();

        $this->assertEquals($attributeLabels, $baseModel->attributeLabels());
    }

    /**
     * @param string $name
     * @param class-string<ActiveQueryInterface> $queryClass
     * @param bool $multiple
     *
     * @dataProvider relationsProvider
     */
    public function testRelations($name, $queryClass, $multiple)
    {
        $class = $this->getBaseClassWrapper();
        /** @var Oauth2BaseActiveRecord $baseModel */
        $baseModel = new $class();

        $relationName = 'get' . ucfirst($name);

        /** @var ActiveQuery $relation */
        $relation = $baseModel->$relationName();

        $this->assertInstanceOf($queryClass, $relation);
        $this->assertEquals($multiple, $relation->multiple);
    }

    /**
     * @return Oauth2BaseActiveRecord|string
     * @throws \ReflectionException
     */
    protected function getBaseClassWrapper()
    {
        $this->mockConsoleApplication();

        $baseModelClass = $this->getBaseModelClass();
        $baseModelReflectionClass = new \ReflectionClass($baseModelClass);

        $wrapperClassShortName = $baseModelReflectionClass->getShortName() . 'TestWrapper';

        if ($baseModelReflectionClass->inNamespace()) {
            $wrapperClassFullName = $baseModelReflectionClass->getNamespaceName() . '\\' . $wrapperClassShortName;
            $wrapperNameSpace =  'namespace ' . $baseModelReflectionClass->getNamespaceName() . ';';
        } else {
            $wrapperClassFullName = $wrapperClassShortName;
        }

        if (!class_exists($wrapperClassFullName, false)) {
            eval(
                ($wrapperNameSpace ?? '') .
                'class ' . $wrapperClassShortName . ' extends \\' . $baseModelClass . ' {' .
                '    public function hasAttribute($name){return false;}' .
                '    public function loadDefaultValues($skipIfSet = true){}' .
                '};'
            );
        }

        return $wrapperClassFullName;
    }
}
