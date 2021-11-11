<?php

namespace rhertogh\Yii2Oauth2Server\interfaces\components\repositories;

use League\OAuth2\Server\Repositories\RefreshTokenRepositoryInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\Oauth2RefreshTokenInterface;
use rhertogh\Yii2Oauth2Server\interfaces\components\repositories\base\Oauth2RepositoryInterface;
use rhertogh\Yii2Oauth2Server\interfaces\components\repositories\base\Oauth2ModelRepositoryInterface;

interface Oauth2RefreshTokenRepositoryInterface extends
    Oauth2RepositoryInterface,
    Oauth2ModelRepositoryInterface,
    RefreshTokenRepositoryInterface
{
    # region RefreshTokenRepositoryInterface methods (overwritten for type covariance)
    /**
     * @inheritDoc
     * @return Oauth2RefreshTokenInterface
     */
    public function getNewRefreshToken();
    # endregion
}
