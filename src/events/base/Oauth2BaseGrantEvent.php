<?php

namespace rhertogh\Yii2Oauth2Server\events\base;

use rhertogh\Yii2Oauth2Server\interfaces\components\server\grants\base\Oauth2GrantTypeInterface;

class Oauth2BaseGrantEvent extends Oauth2BaseEvent
{
    /**
     * @var Oauth2GrantTypeInterface
     */
    public $grant;
}
