<?php

namespace rhertogh\Yii2Oauth2Server\interfaces\components\repositories;

use League\OAuth2\Server\Repositories\AuthCodeRepositoryInterface;
use rhertogh\Yii2Oauth2Server\interfaces\components\repositories\base\Oauth2ModelRepositoryInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\Oauth2AuthCodeInterface;

interface Oauth2AuthCodeRepositoryInterface extends
    Oauth2ModelRepositoryInterface,
    AuthCodeRepositoryInterface
{
    # region Oauth2ModelRepositoryInterface methods (overwritten for type covariance)
    /**
     * @inheritDoc
     * @return Oauth2AuthCodeInterface|null
     */
    public function findModelByPk($pk);

    /**
     * @inheritDoc
     * @return Oauth2AuthCodeInterface|null
     */
    public function findModelByIdentifier($identifier);

    /**
     * @inheritDoc
     * @return Oauth2AuthCodeInterface|null
     */
    public function findModelByPkOrIdentifier($pkOrIdentifier);
    # endregion

    # region AuthCodeRepositoryInterface methods (overwritten for type covariance)
    /**
     * @inheritDoc
     * @return Oauth2AuthCodeInterface
     */
    public function getNewAuthCode();
    # endregion
}
