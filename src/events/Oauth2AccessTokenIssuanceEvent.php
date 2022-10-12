<?php

namespace rhertogh\Yii2Oauth2Server\events;

use rhertogh\Yii2Oauth2Server\events\base\Oauth2BaseGrantEvent;
use rhertogh\Yii2Oauth2Server\interfaces\models\Oauth2AccessTokenInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\Oauth2ClientInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\Oauth2ScopeInterface;

class Oauth2AccessTokenIssuanceEvent extends Oauth2BaseGrantEvent
{
    /**
     * @var \DateInterval
     */
    public \DateInterval $accessTokenTTL;

    /**
     * @var Oauth2ClientInterface
     */
    public Oauth2ClientInterface $client;

    /**
     * @var int|string
     */
    public $userIdentifier;

    /**
     * @var Oauth2ScopeInterface[]
     */
    public $scopes = [];

    /**
     * @var Oauth2AccessTokenInterface|null
     */
    public $accessToken = null;
}
