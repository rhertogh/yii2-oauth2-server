<?php

namespace rhertogh\Yii2Oauth2Server\interfaces\components\server\responses;

use League\OAuth2\Server\ResponseTypes\ResponseTypeInterface;
use rhertogh\Yii2Oauth2Server\Oauth2Module;

interface Oauth2BearerTokenResponseInterface extends ResponseTypeInterface
{
    /**
     * @param Oauth2Module $module
     * @since 1.0.0
     */
    public function __construct(Oauth2Module $module);

    /**
     * Get the module for this response.
     * @return Oauth2Module
     * @since 1.0.0
     */
    public function getModule();
}
