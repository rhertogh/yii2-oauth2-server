<?php

namespace rhertogh\Yii2Oauth2Server\models\traits;

use rhertogh\Yii2Oauth2Server\interfaces\models\base\Oauth2ActiveRecordInterface;

trait Oauth2UserIdentifierTrait
{
    ////////////////////////
    /// Static Functions ///
    ////////////////////////

    /**
     * @inheritDoc
     */
    public static function findAllByUserId($userId)
    {
        return static::find()->where(['user_id' => $userId])->all();
    }

    /////////////////////////
    /// Getters & Setters ///
    /////////////////////////

    /**
     * @inheritDoc
     */
    public function getUserIdentifier()
    {
        return $this->user_id;
    }

    /**
     * @inheritDoc
     */
    public function setUserIdentifier($identifier)
    {
        $this->user_id = $identifier;
    }
}
