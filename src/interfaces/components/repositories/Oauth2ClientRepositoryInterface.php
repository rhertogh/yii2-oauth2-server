<?php

namespace rhertogh\Yii2Oauth2Server\interfaces\components\repositories;

use League\OAuth2\Server\Repositories\ClientRepositoryInterface;
use rhertogh\Yii2Oauth2Server\interfaces\components\repositories\base\Oauth2ModelRepositoryInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\Oauth2ClientInterface;
use yii\base\InvalidConfigException;

interface Oauth2ClientRepositoryInterface extends
    Oauth2ModelRepositoryInterface,
    ClientRepositoryInterface
{
    # region Oauth2ModelRepositoryInterface methods (overwritten for type covariance)
    /**
     * @inheritDoc
     * @return Oauth2ClientInterface|null
     */
    public function findModelByPk($pk);

    /**
     * @inheritDoc
     * @return Oauth2ClientInterface|null
     */
    public function findModelByIdentifier($identifier);

    /**
     * @inheritDoc
     * @return Oauth2ClientInterface|null
     */
    public function findModelByPkOrIdentifier($pkOrIdentifier);
    # endregion

    # region ClientRepositoryInterface methods (overwritten for type covariance)
    /**
     * @inheritDoc
     * @return Oauth2ClientInterface|null
     */
    public function getClientEntity($clientIdentifier);
    # endregion

    /**
     * @param array $filter
     * @return Oauth2ClientInterface[]
     * @throws InvalidConfigException
     */
    public function getAllClients($filter = []);
}
