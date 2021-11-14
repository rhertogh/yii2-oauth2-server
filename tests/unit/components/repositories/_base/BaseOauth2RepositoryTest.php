<?php

namespace Yii2Oauth2ServerTests\unit\components\repositories\_base;

use rhertogh\Yii2Oauth2Server\helpers\DiHelper;
use rhertogh\Yii2Oauth2Server\interfaces\components\repositories\base\Oauth2RepositoryInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\Oauth2AccessTokenInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\Oauth2ClientInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\Oauth2ScopeInterface;
use rhertogh\Yii2Oauth2Server\models\base\Oauth2BaseActiveRecord;
use Yii2Oauth2ServerTests\unit\DatabaseTestCase;

abstract class BaseOauth2RepositoryTest extends DatabaseTestCase
{
    /**
     * @return Oauth2RepositoryInterface|string
     */
    abstract protected function getModelInterface();

    /**
     * @return Oauth2BaseActiveRecord|string
     */
    protected function getModelClass()
    {
        return DiHelper::getValidatedClassName($this->getModelInterface());
    }

    /**
     * @return Oauth2ClientInterface|string
     */
    protected static function getClientClass()
    {
        return DiHelper::getValidatedClassName(Oauth2ClientInterface::class);
    }

    /**
     * @return Oauth2ScopeInterface|string
     */
    protected static function getScopeClass()
    {
        return DiHelper::getValidatedClassName(Oauth2ScopeInterface::class);
    }

    /**
     * @return Oauth2AccessTokenInterface|string
     */
    protected static function getAccessTokenClass()
    {
        return DiHelper::getValidatedClassName(Oauth2AccessTokenInterface::class);
    }
}
