<?php

namespace rhertogh\Yii2Oauth2Server\interfaces\components\repositories\base;

use rhertogh\Yii2Oauth2Server\interfaces\models\base\Oauth2ActiveRecordInterface;
use yii\base\InvalidConfigException;

/**
 * Represents a repository for a model in the Oauth2 module.
 * Note: The Oauth2UserRepositoryInterface does not include this interface
 */
interface Oauth2ModelRepositoryInterface
{
    /**
     * Find a user model by its identifier.
     * @param string $identifier
     * @return Oauth2ActiveRecordInterface|null
     * @throws InvalidConfigException
     * @since 1.0.0
     */
    public function findModelByIdentifier($identifier);
}
