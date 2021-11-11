<?php

namespace Yii2Oauth2ServerTests\unit\components\factories\grants;

use League\OAuth2\Server\Grant\AuthCodeGrant;
use rhertogh\Yii2Oauth2Server\interfaces\components\factories\grants\Oauth2AuthCodeGrantFactoryInterface;

/**
 * @covers \rhertogh\Yii2Oauth2Server\components\factories\grants\Oauth2AuthCodeGrantFactory
 */
class Oauth2AuthCodeGrantFactoryTest extends _base\BaseOauth2AuthCodeGrantFactoryTest
{
    /**
     * @inheritDoc
     */
    public function getFactoryInterface()
    {
        return Oauth2AuthCodeGrantFactoryInterface::class;
    }

    /**
     * @inheritDoc
     */
    public function getGrantTypeClass()
    {
        return AuthCodeGrant::class;
    }
}
