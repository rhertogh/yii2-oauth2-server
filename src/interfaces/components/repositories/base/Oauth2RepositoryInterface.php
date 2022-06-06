<?php

namespace rhertogh\Yii2Oauth2Server\interfaces\components\repositories\base;

use rhertogh\Yii2Oauth2Server\interfaces\models\base\Oauth2ActiveRecordInterface;
use rhertogh\Yii2Oauth2Server\Oauth2Module;

/**
 * Represents the base for all repositories in the Oauth2 module.
 */
interface Oauth2RepositoryInterface
{
    /**
     * Set the module for this response.
     * @param Oauth2Module $module
     * @return $this
     * @since 1.0.0
     */
    public function setModule($module);

    /**
     * Get the class name of the models for this repository.
     * @return class-string<Oauth2ActiveRecordInterface>
     * @since 1.0.0
     */
    public function getModelClass();
}
