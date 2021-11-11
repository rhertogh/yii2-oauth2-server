<?php

namespace Yii2Oauth2ServerTests\unit\components\factories\grants;

use League\OAuth2\Server\Grant\ClientCredentialsGrant;
use rhertogh\Yii2Oauth2Server\interfaces\components\factories\grants\Oauth2ClientCredentialsGrantFactoryInterface;

/**
 * @covers \rhertogh\Yii2Oauth2Server\components\factories\grants\Oauth2ClientCredentialsGrantFactory
 */
class Oauth2ClientCredentialsGrantFactoryTest extends _base\BaseOauth2AuthCodeGrantFactoryTest
{
    /**
     * @inheritDoc
     */
    public function getFactoryInterface()
    {
        return Oauth2ClientCredentialsGrantFactoryInterface::class;
    }

    /**
     * @inheritDoc
     */
    public function getGrantTypeClass()
    {
        return ClientCredentialsGrant::class;
    }
}
