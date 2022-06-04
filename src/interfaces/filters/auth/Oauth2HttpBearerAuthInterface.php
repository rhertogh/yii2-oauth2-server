<?php

namespace rhertogh\Yii2Oauth2Server\interfaces\filters\auth;

use yii\base\Configurable;
use yii\filters\auth\AuthInterface;

interface Oauth2HttpBearerAuthInterface extends
    AuthInterface,
    Configurable
{
}
