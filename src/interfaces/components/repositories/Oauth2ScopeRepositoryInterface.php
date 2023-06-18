<?php

namespace rhertogh\Yii2Oauth2Server\interfaces\components\repositories;

use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Repositories\ScopeRepositoryInterface;
use rhertogh\Yii2Oauth2Server\interfaces\components\repositories\base\Oauth2ModelRepositoryInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\Oauth2ScopeInterface;

interface Oauth2ScopeRepositoryInterface extends
    Oauth2ModelRepositoryInterface,
    ScopeRepositoryInterface
{
    # region ScopeRepositoryInterface methods (overwritten for type covariance)
    /**
     * @inheritDoc
     * @return Oauth2ScopeInterface
     */
    public function getScopeEntityByIdentifier($identifier);

    /**
     * @inheritDoc
     * @return Oauth2ScopeInterface[]
     */
    public function finalizeScopes(
        array $scopes,
        $grantType,
        ClientEntityInterface $clientEntity,
        $userIdentifier = null
    );
    # endregion
}
