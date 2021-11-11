<?php

namespace rhertogh\Yii2Oauth2Server\components\server\grants;

use League\OAuth2\Server\Grant\PasswordGrant;
use rhertogh\Yii2Oauth2Server\interfaces\components\server\grants\Oauth2PasswordGrantInterface;

class Oauth2PasswordGrant extends PasswordGrant implements Oauth2PasswordGrantInterface
{

}
