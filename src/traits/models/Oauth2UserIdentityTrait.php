<?php

namespace rhertogh\Yii2Oauth2Server\traits\models;

use rhertogh\Yii2Oauth2Server\interfaces\models\external\user\Oauth2UserInterface;
use rhertogh\Yii2Oauth2Server\Oauth2Module;
use Yii;
use yii\web\IdentityInterface;

trait Oauth2UserIdentityTrait
{
    /**
     * Returns an ID that can uniquely identify a user identity.
     * @return string|int an ID that uniquely identifies a user identity.
     * @since 1.0.0
     */
    abstract public function getId();

    /**
     * Finds an identity by the given token.
     * @param mixed $token the token to be looked for
     * @param mixed $type the type of the token.
     * @return Oauth2UserInterface|null
     * @since 1.0.0
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        if (property_exists(static::class, 'oauth2ModuleName')) {
            $oauth2ModuleName = static::$oauth2ModuleName;
        }

        /** @var Oauth2Module $module */
        $module = empty($oauth2ModuleName)
            ? Oauth2Module::getInstance()
            : Yii::$app->getModule($oauth2ModuleName);

        return $module->findIdentityByAccessToken($token, $type);
    }

    /**
     * Wrapper for Oauth2 server to expose getId() as getIdentifier()
     * @return int|string|null
     * @since 1.0.0
     */
    public function getIdentifier()
    {
        /** @var IdentityInterface $this */
        return $this->getId();
    }
}
