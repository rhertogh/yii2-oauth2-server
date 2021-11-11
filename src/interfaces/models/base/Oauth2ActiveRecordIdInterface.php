<?php


namespace rhertogh\Yii2Oauth2Server\interfaces\models\base;


interface Oauth2ActiveRecordIdInterface extends Oauth2ActiveRecordInterface
{
    /**
     * Find model by id
     * @param int|string $id
     * @return static|null
     * @since 1.0.0
     */
    public static function findById($id);
}
