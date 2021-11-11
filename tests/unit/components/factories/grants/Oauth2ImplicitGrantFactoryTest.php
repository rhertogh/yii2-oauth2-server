<?php

namespace Yii2Oauth2ServerTests\unit\components\factories\grants;

use League\OAuth2\Server\Grant\ImplicitGrant;
use rhertogh\Yii2Oauth2Server\interfaces\components\factories\grants\Oauth2ImplicitGrantFactoryInterface;

/**
 * @covers \rhertogh\Yii2Oauth2Server\components\factories\grants\Oauth2ImplicitGrantFactory
 */
class Oauth2ImplicitGrantFactoryTest extends _base\BaseOauth2AuthCodeGrantFactoryTest
{
    /**
     * @inheritDoc
     */
    public function getFactoryInterface()
    {
        return Oauth2ImplicitGrantFactoryInterface::class;
    }

    /**
     * @inheritDoc
     */
    public function getGrantTypeClass()
    {
        return ImplicitGrant::class;
    }
}
