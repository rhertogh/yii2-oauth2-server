<?php

namespace rhertogh\Yii2Oauth2Server\events;

use rhertogh\Yii2Oauth2Server\events\base\Oauth2BaseGrantEvent;
use rhertogh\Yii2Oauth2Server\interfaces\models\Oauth2AccessTokenInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\Oauth2RefreshTokenInterface;

class Oauth2RefreshTokenIssuanceEvent extends Oauth2BaseGrantEvent
{
    /**
     * @var Oauth2AccessTokenInterface|null
     */
    public $accessToken = null;

    /**
     * @var Oauth2RefreshTokenInterface|null
     */
    public $refreshToken = null;
}
