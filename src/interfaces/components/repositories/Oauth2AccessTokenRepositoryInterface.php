<?php

namespace rhertogh\Yii2Oauth2Server\interfaces\components\repositories;

use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Repositories\AccessTokenRepositoryInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\base\Oauth2TokenInterface;
use rhertogh\Yii2Oauth2Server\interfaces\components\repositories\base\Oauth2RepositoryInterface;
use rhertogh\Yii2Oauth2Server\interfaces\components\repositories\base\Oauth2ModelRepositoryInterface;

interface Oauth2AccessTokenRepositoryInterface extends
    Oauth2RepositoryInterface,
    Oauth2ModelRepositoryInterface,
    AccessTokenRepositoryInterface
{
    # region AccessTokenRepositoryInterface methods (overwritten for type covariance)
    /**
     * @inheritDoc
     * @return Oauth2TokenInterface
     */
    public function getNewToken(ClientEntityInterface $clientEntity, array $scopes, $userIdentifier = null);
    # endregion

    /**
     * Get the revocation validation for access tokens.
     * @return bool|callable For the behavior of the different types, please see setRevocationValidation()
     * @see setRevocationValidation()
     * @since 1.0.0
     */
    public function getRevocationValidation();

    /**
     * Get the revocation validation for access tokens.
     * @param bool|callable $validation The revocation validation behavior depends on the type/value:
     *  - callable: The callable will be called, its signature should be:
     *              ```php
     *              function(string $tokenIdentifier) {
     *                  return $isValid;
     *              }
     *              ```
     * - boolean:
     *   - true: Oauth2TokenInterface::isTokenRevoked() will be called
     *   - false: revocation validation is disabled.
     *
     * @return $this
     * @since 1.0.0
     */
    public function setRevocationValidation($validation);
}
