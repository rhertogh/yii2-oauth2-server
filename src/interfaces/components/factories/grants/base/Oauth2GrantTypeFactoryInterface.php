<?php

namespace rhertogh\Yii2Oauth2Server\interfaces\components\factories\grants\base;

use League\OAuth2\Server\Grant\GrantTypeInterface;

interface Oauth2GrantTypeFactoryInterface
{
    /**
     * Get the server grant type.
     * @return GrantTypeInterface
     * @since 1.0.0
     */
    public function getGrantType();
}
