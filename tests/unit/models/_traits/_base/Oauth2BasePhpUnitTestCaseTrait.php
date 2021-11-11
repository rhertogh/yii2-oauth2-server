<?php

namespace Yii2Oauth2ServerTests\unit\models\_traits\_base;

trait Oauth2BasePhpUnitTestCaseTrait
{
    abstract public static function assertNull($actual, string $message = ''):void;
    abstract public static function assertEquals($expected, $actual, string $message = ''):void;
    abstract public static function assertInstanceOf(string $expected, $actual, string $message = ''):void;
    abstract public static function assertEmpty($actual, string $message = ''):void;
    abstract public static function assertFalse($actual, string $message = ''):void;
}
