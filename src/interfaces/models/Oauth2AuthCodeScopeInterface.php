<?php

namespace rhertogh\Yii2Oauth2Server\interfaces\models;

use rhertogh\Yii2Oauth2Server\interfaces\models\base\Oauth2ActiveRecordInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\queries\Oauth2AuthCodeScopeQueryInterface;

interface Oauth2AuthCodeScopeInterface extends Oauth2ActiveRecordInterface
{
    /**
     * @inheritDoc
     * @return Oauth2AuthCodeScopeQueryInterface
     */
    public static function find();
}
