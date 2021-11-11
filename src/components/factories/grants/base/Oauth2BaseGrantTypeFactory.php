<?php

namespace rhertogh\Yii2Oauth2Server\components\factories\grants\base;

use rhertogh\Yii2Oauth2Server\interfaces\components\factories\grants\base\Oauth2GrantTypeFactoryInterface;
use rhertogh\Yii2Oauth2Server\Oauth2Module;
use yii\base\Component;

abstract class Oauth2BaseGrantTypeFactory extends Component implements Oauth2GrantTypeFactoryInterface
{
    /**
     * The Time To Live for the access token. When `null` default value of 1 hour is used.
     * The format should be a DateInterval duration (https://www.php.net/manual/en/dateinterval.construct.php).
     * @var string|null
     */
    public $accessTokenTTL = null;

    /**
     * The module for the Grant Type.
     * @var Oauth2Module
     */
    public $module = null;
}
