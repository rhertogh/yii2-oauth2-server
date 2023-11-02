<?php

namespace rhertogh\Yii2Oauth2Server\interfaces\components\repositories\base;

use rhertogh\Yii2Oauth2Server\interfaces\models\base\Oauth2ActiveRecordInterface;
use yii\base\InvalidConfigException;

/**
 * Represents a repository for a model in the Oauth2 module.
 * Note: The Oauth2UserRepositoryInterface does not include this interface
 */
interface Oauth2ModelRepositoryInterface extends Oauth2RepositoryInterface
{
    /**
     * Find a model by its primary key.
     *
     * @param int|string $pk
     * @return Oauth2ActiveRecordInterface|null
     * @throws InvalidConfigException
     * @since 1.0.0
     */
    public function findModelByPk($pk);

    /**
     * Find a model by its identifier.
     *
     * @param string $identifier
     * @return Oauth2ActiveRecordInterface|null
     * @throws InvalidConfigException
     * @since 1.0.0
     */
    public function findModelByIdentifier($identifier);

    /**
     * Find a model by its primary key, and fall back to find by its identifier.
     *
     * @param string $identifier
     * @return Oauth2ActiveRecordInterface|null
     * @throws InvalidConfigException
     * @since 1.0.0
     */
    public function findModelByPkOrIdentifier($pkOrIdentifier);
}
