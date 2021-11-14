<?php

namespace Yii2Oauth2ServerTests\unit\components\factories\grants;

use League\OAuth2\Server\Grant\RefreshTokenGrant;
use rhertogh\Yii2Oauth2Server\interfaces\components\factories\grants\Oauth2RefreshTokenGrantFactoryInterface;

/**
 * @covers \rhertogh\Yii2Oauth2Server\components\factories\grants\Oauth2RefreshTokenGrantFactory
 */
class Oauth2RefreshTokenGrantFactoryTest extends _base\BaseOauth2AuthCodeGrantFactoryTest
{
    /**
     * @inheritDoc
     */
    public function getFactoryInterface()
    {
        return Oauth2RefreshTokenGrantFactoryInterface::class;
    }

    /**
     * @inheritDoc
     */
    public function getGrantTypeClass()
    {
        return RefreshTokenGrant::class;
    }
}
