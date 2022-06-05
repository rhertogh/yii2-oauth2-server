<?php

namespace rhertogh\Yii2Oauth2Server\interfaces\models\external\user;

use rhertogh\Yii2Oauth2Server\interfaces\models\external\user\Oauth2UserInterface;

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
}
