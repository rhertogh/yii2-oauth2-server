<?php

namespace rhertogh\Yii2Oauth2Server\models\queries;

use rhertogh\Yii2Oauth2Server\interfaces\models\queries\Oauth2ScopeQueryInterface;
use rhertogh\Yii2Oauth2Server\models\queries\base\Oauth2BaseActiveQuery;
use rhertogh\Yii2Oauth2Server\models\queries\traits\Oauth2EnabledQueryTrait;

/**
 * This is the ActiveQuery class for [[\rhertogh\Yii2Oauth2Server\models\Oauth2Scope]].
 *
 * @see \rhertogh\Yii2Oauth2Server\models\Oauth2Scope
 */
class Oauth2ScopeQuery extends Oauth2BaseActiveQuery implements Oauth2ScopeQueryInterface
{
    use Oauth2EnabledQueryTrait;
}
