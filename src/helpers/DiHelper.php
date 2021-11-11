<?php

namespace rhertogh\Yii2Oauth2Server\helpers;

use Yii;
use yii\base\InvalidConfigException;
use yii\di\Instance;

class DiHelper
{
    /**
     * Resolve a class or interface to the configured class name.
     * @param $class
     * @return mixed|string
     * @throws InvalidConfigException
     * @since 1.0.0
     */
    public static function getClassName($class)
    {
        if ($class instanceof Instance) {
            return static::getClassName($class->id);
        }

        $definitions = Yii::$container->definitions;

        if (empty($definitions[$class])) {
            return $class;
        }

        $definition = $definitions[$class];

        if (is_callable($definition, true)) {
            $definition = Yii::createObject($class);
        }

        if (is_object($definition)) {
            return get_class($definition);
        }

        if (is_array($definition) && !empty($definition['class'])) {
            return $definition['class'];
        }

        throw new \LogicException('Unknown dependency type: ' . gettype($definition));
    }

    /**
     * Resolves a class name and, in case of an interface, ensure that the concrete class implements the interface.
     * @param $class
     * @return string
     * @throws InvalidConfigException
     * @see getClassName()
     * @since 1.0.0
     */
    public static function getValidatedClassName($class)
    {
        $classDefinition = static::getClassName($class);

        if (interface_exists($class)) {
            if (empty($classDefinition) || $classDefinition === $class) {
                throw new InvalidConfigException($class . ' must be configured in the application dependency injection container.');
            } elseif (!is_a($classDefinition, $class, true)) {
                throw new InvalidConfigException($classDefinition . ' must implement ' . $class);
            }
        } elseif (!class_exists($classDefinition)) {
            throw new InvalidConfigException('Class ' . $classDefinition . ' does not exist.');
        }

        return $classDefinition;
    }
}
