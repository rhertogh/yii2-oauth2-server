<?php

namespace rhertogh\Yii2Oauth2Server\interfaces\models;

use rhertogh\Yii2Oauth2Server\interfaces\models\base\Oauth2ActiveRecordInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\base\Oauth2EnabledInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\queries\Oauth2UserClientScopeQueryInterface;
use yii\db\ActiveQuery;

interface Oauth2UserClientScopeInterface extends
    Oauth2ActiveRecordInterface,
    Oauth2EnabledInterface
{
    /**
     * @inheritDoc
     * @return Oauth2UserClientScopeQueryInterface
     */
    public static function find();
}
