<?php

namespace rhertogh\Yii2Oauth2Server\components\factories\grants\base;

use rhertogh\Yii2Oauth2Server\interfaces\components\factories\grants\base\Oauth2GrantTypeFactoryInterface;
use rhertogh\Yii2Oauth2Server\Oauth2Module;
use rhertogh\Yii2Oauth2Server\traits\DefaultAccessTokenTtlTrait;
use yii\base\Component;

abstract class Oauth2BaseGrantTypeFactory extends Component implements Oauth2GrantTypeFactoryInterface
{
    use DefaultAccessTokenTtlTrait;

    /**
     * The module for the Grant Type.
     * @var Oauth2Module
     */
    public $module = null;
}
