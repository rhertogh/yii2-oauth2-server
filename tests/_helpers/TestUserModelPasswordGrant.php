<?php

namespace Yii2Oauth2ServerTests\_helpers;

use League\OAuth2\Server\Entities\ClientEntityInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\Oauth2PasswordGrantUserInterface;
use Yii;

class TestUserModelPasswordGrant extends TestUserModel implements
    Oauth2PasswordGrantUserInterface # Optional interface, only required when `password` grant type is used
{

    /**
     * @inheritDoc
     */
    public static function findByUsername($username)
    {
        return static::findOne(['username' => $username]);
    }

    /**
     * @inheritDoc
     */
    public function validatePassword($password)
    {
        return Yii::$app->security->validatePassword($password, $this->password_hash);
    }

    /**
     * @inheritDoc
     */
    public function canAccessOauth2ClientAndGrantType(ClientEntityInterface $clientEntity, $grantType)
    {
        return true;
    }
}
