<?php

namespace rhertogh\Yii2Oauth2Server\components\repositories;

use League\OAuth2\Server\Entities\ClientEntityInterface;
use rhertogh\Yii2Oauth2Server\components\repositories\base\Oauth2BaseRepository;
use rhertogh\Yii2Oauth2Server\interfaces\components\repositories\Oauth2UserRepositoryInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\external\user\Oauth2PasswordGrantUserInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\external\user\Oauth2UserInterface;
use yii\base\InvalidConfigException;

class Oauth2UserRepository extends Oauth2BaseRepository implements Oauth2UserRepositoryInterface
{
    /**
     * @inheritDoc
     * @return class-string<Oauth2UserInterface>
     */
    public function getModelClass()
    {
        return $this->_module->identityClass;
    }

    /**
     * @inheritDoc
     */
    public function getUserEntityByIdentifier($identifier)
    {
        $userClass = $this->getModelClass();
        $user = $userClass::findIdentity($identifier);

        if (empty($user)) {
            return null;
        }

        if (!($user instanceof Oauth2UserInterface)) {
            throw new \TypeError(
                $userClass . '::findIdentity() must return an instance of ' . Oauth2UserInterface::class
            );
        }

        return $user;
    }

    /**
     * @inheritDoc
     * @throws InvalidConfigException
     */
    public function getUserEntityByUserCredentials(
        $username,
        $password,
        $grantType,
        ClientEntityInterface $clientEntity
    ) {
        $userClass = $this->getModelClass();
        if (!is_a($userClass, Oauth2PasswordGrantUserInterface::class, true)) {
            throw new \TypeError(
                'In order to support the `password` grant type, ' . $userClass
                    . ' must implement ' . Oauth2PasswordGrantUserInterface::class
            );
        }

        /** @var Oauth2UserInterface|null $user */
        $user = $userClass::findByUsername($username);
        if (empty($user)) {
            return null;
        }
        if (!($user instanceof Oauth2UserInterface)) {
            throw new \TypeError(
                $userClass . '::findByUsername() must return an instance of ' . Oauth2UserInterface::class
            );
        }
        if (!($user instanceof Oauth2PasswordGrantUserInterface)) {
            throw new \TypeError(
                'In order to support the `password` grant type, ' . $userClass
                    . '::findByUsername() must return an instance of ' . Oauth2PasswordGrantUserInterface::class
            );
        }

        if ($user->validatePassword($password)) {
            return $user;
        }

        return null;
    }
}
