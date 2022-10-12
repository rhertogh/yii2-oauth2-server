<?php

namespace rhertogh\Yii2Oauth2Server\components\server\grants;

use League\OAuth2\Server\Grant\ImplicitGrant;
use rhertogh\Yii2Oauth2Server\components\server\grants\traits\Oauth2GrantTrait;
use rhertogh\Yii2Oauth2Server\interfaces\components\server\grants\Oauth2ImplicitGrantInterface;

class Oauth2ImplicitGrant extends ImplicitGrant implements Oauth2ImplicitGrantInterface
{
    use Oauth2GrantTrait;
}
