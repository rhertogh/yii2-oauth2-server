<?php

namespace rhertogh\Yii2Oauth2Server\interfaces\models\queries;

// phpcs:disable Generic.Files.LineLength.TooLong
use rhertogh\Yii2Oauth2Server\interfaces\models\Oauth2AccessTokenScopeInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\queries\base\BatchQueryResult\Oauth2AccessTokenScopeBatchQueryResultInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\queries\base\Oauth2BaseActiveQueryInterface;

// phpcs:enable Generic.Files.LineLength.TooLong

interface Oauth2AccessTokenScopeQueryInterface extends Oauth2BaseActiveQueryInterface
{
    /**
     * @inheritDoc
     * @return Oauth2AccessTokenScopeInterface
     */
    public function one($db = null);

    /**
     * @inheritDoc
     * @return Oauth2AccessTokenScopeInterface[]
     */
    public function all($db = null);

    /**
     * @inheritDoc
     * @return Oauth2AccessTokenScopeBatchQueryResultInterface
     */
    public function each($batchSize = 100, $db = null);
}
