<?php

namespace rhertogh\Yii2Oauth2Server\interfaces\components\repositories;

use League\OAuth2\Server\Repositories\AuthCodeRepositoryInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\Oauth2AuthCodeInterface;
use rhertogh\Yii2Oauth2Server\interfaces\components\repositories\base\Oauth2RepositoryInterface;
use rhertogh\Yii2Oauth2Server\interfaces\components\repositories\base\Oauth2ModelRepositoryInterface;

interface Oauth2AuthCodeRepositoryInterface extends
    Oauth2RepositoryInterface,
    Oauth2ModelRepositoryInterface,
    AuthCodeRepositoryInterface
{
    # region AuthCodeRepositoryInterface methods (overwritten for type covariance)
    /**
     * @inheritDoc
     * @return Oauth2AuthCodeInterface
     */
    public function getNewAuthCode();
    # endregion
}
