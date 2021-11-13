<?php

namespace rhertogh\Yii2Oauth2Server\models;

use rhertogh\Yii2Oauth2Server\interfaces\models\Oauth2UserClientScopeInterface;
use rhertogh\Yii2Oauth2Server\models\traits\Oauth2EnabledTrait;

class Oauth2UserClientScope extends base\Oauth2UserClientScope implements Oauth2UserClientScopeInterface
{
    use Oauth2EnabledTrait;
}
