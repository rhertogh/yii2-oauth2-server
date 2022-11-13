<?php

namespace Yii2Oauth2ServerTests\unit\components\factories\grants;

use rhertogh\Yii2Oauth2Server\components\server\grants\Oauth2PersonalAccessTokenGrant;
use rhertogh\Yii2Oauth2Server\interfaces\components\factories\grants\Oauth2PersonalAccessTokenGrantFactoryInterface;

/**
 * @covers \rhertogh\Yii2Oauth2Server\components\factories\grants\Oauth2AuthCodeGrantFactory
 */
class Oauth2PersonalAccessTokenGrantFactoryTest extends _base\BaseOauth2AuthCodeGrantFactoryTest
{
    /**
     * @inheritDoc
     */
    public function getFactoryInterface()
    {
        return Oauth2PersonalAccessTokenGrantFactoryInterface::class;
    }

    /**
     * @inheritDoc
     */
    public function getGrantTypeClass()
    {
        return Oauth2PersonalAccessTokenGrant::class;
    }
}
