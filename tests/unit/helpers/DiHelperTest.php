<?php

namespace Yii2Oauth2ServerTests\unit\helpers;

use rhertogh\Yii2Oauth2Server\helpers\DiHelper;
use Yii;
use yii\di\Instance;
use Yii2Oauth2ServerTests\unit\TestCase;

/**
 * @covers \rhertogh\Yii2Oauth2Server\helpers\DiHelper
 */
class DiHelperTest extends TestCase
{

    /**
     * @param $className
     * @param $definition
     *
     * @dataProvider getClassNameProvider
     */
    public function testGetClassName($className, $definition, $expected)
    {
        Yii::$container->set($className, $definition);
        $this->assertEquals($expected, DiHelper::getClassName($className));
    }

    /**
     * @return array[]
     * @see testGetClassName()
     */
    public function getClassNameProvider()
    {
        return [
            ['test-class', 'test-definition', 'test-definition'],
            ['test-class', new \stdClass(), 'stdClass'],
            ['test-class', fn() => new \stdClass(), 'stdClass'],
        ];
    }

    public function testGetClassNameProviderWithInstance()
    {
        $className = 'test-class';
        $definition = 'test';
        Yii::$container->set($className, $definition);
        $this->assertEquals($definition, DiHelper::getClassName(Instance::of($className)));
    }

    public function testGetClassNameForUndefinedClass()
    {
        $className = 'does-not-exist';
        $this->assertEquals($className, DiHelper::getClassName($className));
    }

    public function testGetClassNameForUnknownDefinitionType()
    {
        $this->setInaccessibleProperty(Yii::$container, '_definitions', ['invalid' => 123]);
        $this->expectExceptionMessage('Unknown dependency type: integer');
        DiHelper::getClassName('invalid');
    }

    public function testGetValidatedClassName()
    {
        Yii::$container->set(\Throwable::class, \Exception::class);
        $this->assertEquals(\Exception::class, DiHelper::getValidatedClassName(\Throwable::class));
    }

    public function testGetValidatedClassNameUndefinedClassForInterface()
    {
        $this->expectExceptionMessage('ArrayAccess must be configured in the application dependency injection container.');
        DiHelper::getValidatedClassName(\ArrayAccess::class);
    }

    public function testGetValidatedClassNameInvalidClassForInterface()
    {
        Yii::$container->set(\ArrayAccess::class, \stdClass::class);
        $this->expectExceptionMessage('stdClass must implement ArrayAccess');
        DiHelper::getValidatedClassName(\ArrayAccess::class);
    }

    public function testGetValidatedClassNameForUndefinedClass()
    {
        $className = 'does-not-exist';
        $this->expectExceptionMessage('Class ' . $className . ' does not exist.');
        DiHelper::getValidatedClassName($className);
    }
}
