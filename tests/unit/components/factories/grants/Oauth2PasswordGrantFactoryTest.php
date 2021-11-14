<?php

namespace Yii2Oauth2ServerTests\unit\components\factories\grants;

use League\OAuth2\Server\Grant\PasswordGrant;
use rhertogh\Yii2Oauth2Server\interfaces\components\factories\grants\Oauth2PasswordGrantFactoryInterface;

/**
 * @covers \rhertogh\Yii2Oauth2Server\components\factories\grants\Oauth2PasswordGrantFactory
 */
class Oauth2PasswordGrantFactoryTest extends _base\BaseOauth2AuthCodeGrantFactoryTest
{
    /**
     * @inheritDoc
     */
    public function getFactoryInterface()
    {
        return Oauth2PasswordGrantFactoryInterface::class;
    }

    /**
     * @inheritDoc
     */
    public function getGrantTypeClass()
    {
        return PasswordGrant::class;
    }
}
