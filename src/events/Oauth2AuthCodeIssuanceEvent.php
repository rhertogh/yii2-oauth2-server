<?php

namespace rhertogh\Yii2Oauth2Server\events;

use rhertogh\Yii2Oauth2Server\events\base\Oauth2BaseGrantEvent;
use rhertogh\Yii2Oauth2Server\interfaces\models\Oauth2AuthCodeInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\Oauth2ClientInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\Oauth2ScopeInterface;

class Oauth2AuthCodeIssuanceEvent extends Oauth2BaseGrantEvent
{
    /**
     * @var \DateInterval
     */
    public \DateInterval $authCodeTTL;

    /**
     * @var Oauth2ClientInterface
     */
    public Oauth2ClientInterface $client;

    /**
     * @var int|string
     */
    public $userIdentifier;

    /**
     * @var string|null
     */
    public $redirectUri;

    /**
     * @var Oauth2ScopeInterface[]
     */
    public $scopes = [];

    /**
     * @var Oauth2AuthCodeInterface|null
     */
    public $authCode = null;
}
