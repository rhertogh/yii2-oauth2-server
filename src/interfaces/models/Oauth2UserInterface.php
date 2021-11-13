<?php

namespace rhertogh\Yii2Oauth2Server\interfaces\models;

use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Entities\UserEntityInterface;
use yii\db\TableSchema;
use yii\web\IdentityInterface;

interface Oauth2UserInterface extends
    UserEntityInterface
{
    /**
     * Get the table schema for the model. If the model does not have a table this method should return `null`.
     * @return TableSchema|null
     * @since 1.0.0
     */
    public static function getTableSchema();

    /**
     * Finds an identity by the given ID.
     * @param string|int $id the ID to be looked for
     * @return Oauth2UserInterface|IdentityInterface|null the identity object that matches the given ID.
     * Null should be returned if such an identity cannot be found
     * or the identity is not in an active state (disabled, deleted, etc.)
     * @since 1.0.0
     */
    public static function findIdentity($id);
}
