<?php

namespace rhertogh\Yii2Oauth2Server\interfaces\components\repositories;

use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Repositories\UserRepositoryInterface;
use rhertogh\Yii2Oauth2Server\interfaces\components\repositories\base\Oauth2RepositoryInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\Oauth2PasswordGrantUserInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\Oauth2UserInterface;

interface Oauth2UserRepositoryInterface extends
    Oauth2RepositoryInterface,
    UserRepositoryInterface
{
    /**
     * @param string|int $identifier
     * @return Oauth2UserInterface|null
     * @since 1.0.0
     */
    public function getUserEntityByIdentifier($identifier);

    # region UserRepositoryInterface methods (overwritten for type covariance)
    /**
     * @inheritdoc
     * @return Oauth2PasswordGrantUserInterface|null
     */
    public function getUserEntityByUserCredentials(
        $username,
        $password,
        $grantType,
        ClientEntityInterface $clientEntity
    );
    # endregion
}
