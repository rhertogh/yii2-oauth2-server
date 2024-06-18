<?php

namespace rhertogh\Yii2Oauth2Server\interfaces\models\base;

interface Oauth2UserIdentifierInterface
{
    /**
     * Find all models by user identifier
     *
     * @param string|int $userId
     * @return static[]|Oauth2ActiveRecordInterface
     * @since 1.0.0
     */
    public static function findAllByUserId($userId);

    /**
     * Set the identifier of the user associated with the token.
     *
     * @param string|int|null $identifier The identifier of the user
     */
    public function setUserIdentifier($identifier);

    /**
     * Get the token user's identifier.
     *
     * @return string|int|null
     */
    public function getUserIdentifier();
}
