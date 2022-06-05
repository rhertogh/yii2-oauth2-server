<?php

namespace rhertogh\Yii2Oauth2Server\interfaces\models\queries;

// phpcs:disable Generic.Files.LineLength.TooLong
use rhertogh\Yii2Oauth2Server\interfaces\models\Oauth2ScopeInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\queries\base\BatchQueryResult\Oauth2ScopeBatchQueryResultInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\queries\base\Oauth2BaseActiveQueryInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\queries\base\Oauth2EnabledQueryInterface;

// phpcs:enable Generic.Files.LineLength.TooLong

interface Oauth2ScopeQueryInterface extends
    Oauth2EnabledQueryInterface,
    Oauth2BaseActiveQueryInterface
{
    /**
     * @inheritDoc
     * @return Oauth2ScopeInterface|array|null
     */
    public function one($db = null);

    /**
     * @inheritDoc
     * @return Oauth2ScopeInterface[]
     */
    public function all($db = null);

    /**
     * @inheritDoc
     * @return Oauth2ScopeBatchQueryResultInterface
     */
    public function each($batchSize = 100, $db = null);
}
