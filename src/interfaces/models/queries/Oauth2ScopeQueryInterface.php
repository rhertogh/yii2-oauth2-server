<?php

namespace rhertogh\Yii2Oauth2Server\interfaces\models\queries;

use rhertogh\Yii2Oauth2Server\interfaces\models\queries\base\Oauth2EnabledQueryInterface;
use yii\db\ActiveQueryInterface;

interface Oauth2ScopeQueryInterface extends
    Oauth2EnabledQueryInterface,
    ActiveQueryInterface
{
}
