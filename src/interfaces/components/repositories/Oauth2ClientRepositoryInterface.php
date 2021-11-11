<?php

namespace rhertogh\Yii2Oauth2Server\interfaces\components\repositories;

use League\OAuth2\Server\Repositories\ClientRepositoryInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\Oauth2ClientInterface;
use rhertogh\Yii2Oauth2Server\interfaces\components\repositories\base\Oauth2RepositoryInterface;
use rhertogh\Yii2Oauth2Server\interfaces\components\repositories\base\Oauth2ModelRepositoryInterface;

interface Oauth2ClientRepositoryInterface extends
    Oauth2RepositoryInterface,
    Oauth2ModelRepositoryInterface,
    ClientRepositoryInterface
{
    # region ClientRepositoryInterface methods (overwritten for type covariance)
    /**
     * @inheritDoc
     * @return Oauth2ClientInterface
     */
    public function getClientEntity($clientIdentifier);
    # endregion
}
