<?php


namespace rhertogh\Yii2Oauth2Server\interfaces\models;


use rhertogh\Yii2Oauth2Server\interfaces\models\base\Oauth2ActiveRecordInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\queries\Oauth2AccessTokenScopeQueryInterface;
use yii\db\ActiveQuery;

interface Oauth2AccessTokenScopeInterface extends Oauth2ActiveRecordInterface
{
    /**
     * @inheritDoc
     * @return Oauth2AccessTokenScopeQueryInterface|ActiveQuery
     */
    public static function find();
}
