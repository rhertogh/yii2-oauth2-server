<?php

namespace Yii2Oauth2ServerTests\unit\components\factories\grants\_base;

use League\OAuth2\Server\Grant\GrantTypeInterface;
use rhertogh\Yii2Oauth2Server\interfaces\components\factories\grants\base\Oauth2GrantTypeFactoryInterface;
use rhertogh\Yii2Oauth2Server\Oauth2Module;
use Yii;
use Yii2Oauth2ServerTests\unit\TestCase;

abstract class BaseOauth2AuthCodeGrantFactoryTest extends TestCase
{
    /**
     * @return class-string<Oauth2GrantTypeFactoryInterface>
     */
    abstract public function getFactoryInterface();

    /**
     * @return class-string<GrantTypeInterface>
     */
    abstract public function getGrantTypeClass();

    public function testGetGrantType()
    {
        $this->mockConsoleApplication();
        /** @var Oauth2GrantTypeFactoryInterface $factory */
        $factory = Yii::createObject([
            'class' => $this->getFactoryInterface(),
            'module' => Oauth2Module::getInstance(),
        ]);
        $this->assertInstanceOf($this->getGrantTypeClass(), $factory->getGrantType());
    }
}
