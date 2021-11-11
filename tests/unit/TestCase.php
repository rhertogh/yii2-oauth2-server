<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace Yii2Oauth2ServerTests\unit;

use League\OAuth2\Server\CryptKey;
use rhertogh\Yii2Oauth2Server\components\server\Oauth2ResourceServer;
use rhertogh\Yii2Oauth2Server\interfaces\components\repositories\Oauth2AccessTokenRepositoryInterface;
use Yii;
use yii\di\Container;
use yii\helpers\ArrayHelper;
use yii\helpers\ReplaceArrayValue;
use yii\helpers\UnsetArrayValue;
use Yii2Oauth2ServerTests\_helpers\BearerTokenValidatorHelper;

/**
 * This is the base class for all yii framework unit tests.
 */
abstract class TestCase extends \Codeception\Test\Unit
{
    public static $params;

    public $validAccessToken = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiJ0ZXN0LWNsaWVudC10eXBlLWF1dGgtY29kZS12YWxpZCIsImp0aSI6ImUyNzU5MWMxYzNjMzUwYjM0ODRhY2JiYjU5NmU5OTkxYzVhZWVlMDRjOWFiZjhkNjE4OGUwZGFjNjI3MzQzMjVmOTA0YzljZTkyMjEzNGJlIiwiaWF0IjoxNjMwODU1MTgxLjE2NTk1NCwibmJmIjoxNjMwODU1MTgxLjE2NTk2LCJleHAiOjQ3ODY1Mjg3ODEsInN1YiI6IjEyMyIsInNjb3BlcyI6WyJ1c2VyLnVzZXJuYW1lLnJlYWQiLCJ1c2VyLmVtYWlsX2FkZHJlc3MucmVhZCJdfQ.dMR-0At3PvjzwWyzcM0P5z_Z7jjrbZKW-aRKpa4_hquS-1uLyxOKjU2wqgaEzXRqqLWnUF8fwbj_DEQsPA8su6hI0B_hyKI_hKO4DzAUNLKIthwqFsyiAh4Ksj1cc5-tFfmrqMMgRkQqSuKPSzk_cnIQEYw9hKUjbmqotgHFGEIwmoMzry1bvbD6JCO766JHOvaFMHOmKNAgSf9REeSCdlmWMtT0ScY9qUNqcDcQpp-pndiYvGteQ-jrU0Gah1L6fMXLgGzjTU6k6UbPXlSmG2FsDVFa6zpA8vbzFWoG1H4ql2kXVclUdJ2rMX8FoTJ__qaRgJXTz8rYDV58LTIzhQ';

    public $privateCryptKeyPath = '@Yii2Oauth2ServerTests/_config/keys/private.key';
    public $privateCryptKeyPassPhrase = 'secret';
    public $publicCryptKeyPath = '@Yii2Oauth2ServerTests/_config/keys/public.key';

    /**
     * Returns a test configuration param from /_data/config.php.
     * @param string $name params name
     * @param mixed $default default value to use when param is not set.
     * @return mixed  the value of the configuration param
     */
    public static function getParam($name, $default = null)
    {
        if (static::$params === null) {
            static::$params = require(Yii::getAlias('@Yii2Oauth2ServerTests/_data/config.php'));
        }

        return isset(static::$params[$name]) ? static::$params[$name] : $default;
    }

    protected function _before()
    {
        $this->destroyApplication();
    }

    /**
     * Clean up after test.
     * By default the application created with [[mockApplication]] will be destroyed.
     */
    protected function _after()
    {
        parent::_after();

        $logger = Yii::getLogger();
        $logger->flush();

        $this->destroyApplication();
    }

    /**
     * Populates Yii::$app with a new application
     * The application will be destroyed on tearDown() automatically.
     * @param array $config The application configuration, if needed
     * @param string $appClass name of the application class to create
     */
    protected function mockConsoleApplication($config = [], $appClass = '\yii\console\Application')
    {
        new $appClass($this->getMockConsoleAppConfig($config));
    }

    protected function mockApplicationWithoutOauth2($config = [], $appClass = '\yii\console\Application')
    {
        $this->mockConsoleApplication(
            ArrayHelper::merge(
                [
                    'bootstrap' => new ReplaceArrayValue([]),
                    'modules' => [
                        'oauth2' => new UnsetArrayValue(),
                    ],
                ],
                $config
            ),
            $appClass
        );
    }

    protected function mockWebApplication($config = [], $appClass = '\yii\web\Application')
    {
        new $appClass($this->getMockWebAppConfig($config));

        $_SERVER['REQUEST_URI'] = 'https://localhost';
    }

    /**
     * @param array $config
     * @return array
     */
    protected function getMockBaseAppConfig($config = []): array
    {
        return [];
    }

    /**
     * @param array $config
     * @return array
     */
    protected function getMockConsoleAppConfig($config = []): array
    {
        return ArrayHelper::merge(
            require(Yii::getAlias('@Yii2Oauth2ServerTests/_config/main.php')),
            $this->getMockBaseAppConfig(),
            $config
        );
    }

    /**
     * @param array $config
     * @return array
     */
    protected function getMockWebAppConfig($config = []): array
    {
        return ArrayHelper::merge(
            require(Yii::getAlias('@Yii2Oauth2ServerTests/_config/site.php')),
            [
                'basePath' => __DIR__,
                'vendorPath' => dirname(__DIR__) . '/vendor',
                'components' => [
                    'request' => [
                        'cookieValidationKey' => 'wefJDF8sfdsfSDefwqdxj9oq',
                        'scriptFile' => __DIR__ . '/index.php',
                        'scriptUrl' => '/index.php',
                    ],
                ],
            ],
            $this->getMockBaseAppConfig(),
            $config);
    }

    /**
     * Destroys application in Yii::$app by setting it to null.
     */
    protected function destroyApplication()
    {
        Yii::$app = null;
        Yii::$container = new Container();
    }

    /**
     * Invokes object method, even if it is private or protected.
     * @param object $object object.
     * @param string $method method name.
     * @param array $args method arguments
     * @return mixed method result
     * @throws \ReflectionException
     */
    protected function invoke($object, $method, array $args = [])
    {
        $classReflection = new \ReflectionClass(get_class($object));
        $methodReflection = $classReflection->getMethod($method);
        $methodReflection->setAccessible(true);
        $result = $methodReflection->invokeArgs($object, $args);
        $methodReflection->setAccessible(false);
        return $result;
    }

    /**
     * Sets an inaccessible object property to a designated value.
     * @param $object
     * @param $propertyName
     * @param $value
     * @param bool $revoke whether to make property inaccessible after setting
     * @since 2.0.11
     */
    protected function setInaccessibleProperty($object, $propertyName, $value, $revoke = true)
    {
        $class = new \ReflectionClass($object);
        while (!$class->hasProperty($propertyName)) {
            $class = $class->getParentClass();
        }
        $property = $class->getProperty($propertyName);
        $property->setAccessible(true);
        $property->setValue($object, $value);
        if ($revoke) {
            $property->setAccessible(false);
        }
    }

    /**
     * Gets an inaccessible object property.
     * @param $object
     * @param $propertyName
     * @param bool $revoke whether to make property inaccessible after getting
     * @return mixed
     */
    protected function getInaccessibleProperty($object, $propertyName, $revoke = true)
    {
        $class = new \ReflectionClass($object);
        while (!$class->hasProperty($propertyName)) {
            $class = $class->getParentClass();
        }
        $property = $class->getProperty($propertyName);
        $property->setAccessible(true);
        $result = $property->getValue($object);
        if ($revoke) {
            $property->setAccessible(false);
        }

        return $result;
    }

    /**
     * Gets an inaccessible class constant.
     * @param $class
     * @param $constantName
     * @return mixed
     */
    protected function getInaccessibleConstant($class, $constantName)
    {
        $reflectionClass = new \ReflectionClass($class);
        while (!$reflectionClass->hasConstant($constantName)) {
            $reflectionClass = $reflectionClass->getParentClass();
        }
        return $reflectionClass->getConstant($constantName);
    }

    protected function callInaccessibleMethod($object, $method, $args = [], $revoke = true) {
        $class = new \ReflectionClass(get_class($object));
        $method = $class->getMethod($method);
        $method->setAccessible(true);
        $result = $method->invokeArgs($object, $args);
        if ($revoke) {
            $method->setAccessible(false);
        }
        return $result;
    }

    /**
     * Changes db component config
     * @param $db
     */
    protected function switchDbConnection($db)
    {
        $databases = $this->getParam('databases');
        if (isset($databases[$db])) {
            $database = $databases[$db];
            Yii::$app->db->close();
            Yii::$app->db->dsn = isset($database['dsn']) ? $database['dsn'] : null;
            Yii::$app->db->username = isset($database['username']) ? $database['username'] : null;
            Yii::$app->db->password = isset($database['password']) ? $database['password'] : null;
        }
    }

    /**
     * @return CryptKey
     */
    protected function getMockPrivateCryptKey()
    {
        $keyPath = Yii::getAlias($this->privateCryptKeyPath);
        chmod($keyPath, 0660);
        return New CryptKey($keyPath, $this->privateCryptKeyPassPhrase);
    }

    /**
     * @return CryptKey
     */
    protected function getMockPublicCryptKey()
    {
        $keyPath = Yii::getAlias($this->publicCryptKeyPath);
        chmod($keyPath, 0660);
        return New CryptKey($keyPath);
    }

    /**
     * @return Oauth2ResourceServer
     */
    protected function getMockResourceServer()
    {
        return new Oauth2ResourceServer(
            Yii::createObject(Oauth2AccessTokenRepositoryInterface::class),
            $this->getMockPublicCryptKey()
        );
    }

    protected function getBearerTokenValidatorHelper()
    {
        $bearerTokenValidatorHelper = new BearerTokenValidatorHelper(Yii::createObject(Oauth2AccessTokenRepositoryInterface::class));
        $bearerTokenValidatorHelper->setPublicKey($this->getMockPublicCryptKey());
        return $bearerTokenValidatorHelper;
    }
}
