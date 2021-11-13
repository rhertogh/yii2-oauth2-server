<?php

namespace rhertogh\Yii2Oauth2Server\interfaces\models;

use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Entities\UserEntityInterface;
use yii\db\TableSchema;

interface Oauth2PasswordGrantUserInterface extends Oauth2UserInterface
{
    /**
     * Finds a user by username.
     * @return static
     * @since 1.0.0
     */
    public static function findByUsername($username);

    /**
     * Validates the user password.
     * @return bool
     * @since 1.0.0
     */
    public function validatePassword($password);

    /**
     * Validates the user's access to the specified client and grant type.
     * @return bool
     * @since 1.0.0
     */
    public function canAccessOauth2ClientAndGrantType(ClientEntityInterface $clientEntity, $grantType);
}
