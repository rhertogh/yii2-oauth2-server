<?php

namespace rhertogh\Yii2Oauth2Server\interfaces\models;

use rhertogh\Yii2Oauth2Server\interfaces\models\base\Oauth2ActiveRecordInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\base\Oauth2EnabledInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\base\Oauth2UserIdentifierInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\queries\Oauth2UserClientQueryInterface;

interface Oauth2UserClientInterface extends
    Oauth2ActiveRecordInterface,
    Oauth2EnabledInterface,
    Oauth2UserIdentifierInterface
{
    /**
     * @inheritDoc
     * @return Oauth2UserClientQueryInterface
     */
    public static function find();
}
