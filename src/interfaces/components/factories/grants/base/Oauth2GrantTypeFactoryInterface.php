<?php

namespace rhertogh\Yii2Oauth2Server\interfaces\components\factories\grants\base;

use League\OAuth2\Server\Grant\GrantTypeInterface;
use rhertogh\Yii2Oauth2Server\interfaces\components\common\DefaultAccessTokenTtlInterface;

interface Oauth2GrantTypeFactoryInterface extends DefaultAccessTokenTtlInterface
{
    /**
     * Get the server grant type.
     * @return GrantTypeInterface
     * @since 1.0.0
     */
    public function getGrantType();
}
