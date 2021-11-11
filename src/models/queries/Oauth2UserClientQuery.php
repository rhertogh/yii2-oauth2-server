<?php

namespace rhertogh\Yii2Oauth2Server\models\queries;

use rhertogh\Yii2Oauth2Server\interfaces\models\queries\Oauth2UserClientQueryInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\queries\Oauth2UserClientScopeQueryInterface;
use rhertogh\Yii2Oauth2Server\models\queries\base\Oauth2BaseActiveQuery;

/**
 * This is the ActiveQuery class for [[\rhertogh\Yii2Oauth2Server\models\Oauth2UserClient]].
 *
 * @see \rhertogh\Yii2Oauth2Server\models\Oauth2UserClientScope
 */
class Oauth2UserClientQuery extends Oauth2BaseActiveQuery implements Oauth2UserClientQueryInterface
{

}
