<?php

namespace rhertogh\Yii2Oauth2Server\interfaces\models;

use rhertogh\Yii2Oauth2Server\interfaces\models\base\Oauth2ActiveRecordInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\queries\Oauth2AccessTokenScopeQueryInterface;

interface Oauth2AccessTokenScopeInterface extends Oauth2ActiveRecordInterface
{
    /**
     * @inheritDoc
     * @return Oauth2AccessTokenScopeQueryInterface
     */
    public static function find();
}
