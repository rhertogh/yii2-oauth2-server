<?php

namespace rhertogh\Yii2Oauth2Server\models\queries;

use rhertogh\Yii2Oauth2Server\interfaces\models\queries\Oauth2ClientScopeQueryInterface;
use rhertogh\Yii2Oauth2Server\models\queries\base\Oauth2BaseActiveQuery;
use rhertogh\Yii2Oauth2Server\models\queries\traits\Oauth2EnabledQueryTrait;
use rhertogh\Yii2Oauth2Server\models\traits\Oauth2EnabledTrait;

/**
 * This is the ActiveQuery class for [[\rhertogh\Yii2Oauth2Server\models\Oauth2ClientScope]].
 *
 * @see \rhertogh\Yii2Oauth2Server\models\Oauth2ClientScope
 */
class Oauth2ClientScopeQuery extends Oauth2BaseActiveQuery implements Oauth2ClientScopeQueryInterface
{
    use Oauth2EnabledQueryTrait;
}
