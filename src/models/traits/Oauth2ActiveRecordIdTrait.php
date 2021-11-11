<?php

namespace rhertogh\Yii2Oauth2Server\models\traits;

trait Oauth2ActiveRecordIdTrait
{
    /**
     * @inheritDoc
     */
    public static function findById($id)
    {
        return static::findOne(['id' => $id]);
    }
}
