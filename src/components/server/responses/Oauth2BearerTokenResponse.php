<?php

namespace rhertogh\Yii2Oauth2Server\components\server\responses;

use League\OAuth2\Server\ResponseTypes\BearerTokenResponse;
use rhertogh\Yii2Oauth2Server\interfaces\components\server\responses\Oauth2BearerTokenResponseInterface;
use rhertogh\Yii2Oauth2Server\Oauth2Module;

class Oauth2BearerTokenResponse extends BearerTokenResponse implements Oauth2BearerTokenResponseInterface
{
    /**
     * @var Oauth2Module
     * @since 1.0.0
     */
    protected $_module;

    /**
     * @inheritDoc
     */
    public function __construct(Oauth2Module $module)
    {
        $this->_module = $module;
    }

    /**
     * @inheritDoc
     */
    public function getModule()
    {
        return $this->_module;
    }
}
