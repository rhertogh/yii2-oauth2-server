<?php

namespace rhertogh\Yii2Oauth2Server\interfaces\components\repositories;

use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Repositories\UserRepositoryInterface;
use rhertogh\Yii2Oauth2Server\interfaces\components\repositories\base\Oauth2RepositoryInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\external\user\Oauth2PasswordGrantUserInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\external\user\Oauth2UserInterface;

interface Oauth2UserRepositoryInterface extends
    Oauth2RepositoryInterface,
    UserRepositoryInterface
{
    /**
     * @param string|int $identifier
     * @return Oauth2UserInterface|null
     * @since 1.0.0
     */
    public function getUserEntityByIdentifier(string $identifier): ?Oauth2UserInterface;

    # region UserRepositoryInterface methods (overwritten for type covariance)
    /**
     * @inheritdoc
     * @return Oauth2PasswordGrantUserInterface|null
     */
    public function getUserEntityByUserCredentials(
        string $username,
        string $password,
        string $grantType,
        ClientEntityInterface $clientEntity
    ): ?Oauth2PasswordGrantUserInterface;
    # endregion
}
