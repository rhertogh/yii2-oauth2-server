<?php

namespace rhertogh\Yii2Oauth2Server\interfaces\components\repositories;

use League\OAuth2\Server\Repositories\RefreshTokenRepositoryInterface;
use rhertogh\Yii2Oauth2Server\interfaces\components\repositories\base\Oauth2ModelRepositoryInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\Oauth2RefreshTokenInterface;

interface Oauth2RefreshTokenRepositoryInterface extends
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
