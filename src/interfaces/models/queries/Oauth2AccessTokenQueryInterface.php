<?php

namespace rhertogh\Yii2Oauth2Server\interfaces\models\queries;

// phpcs:disable Generic.Files.LineLength.TooLong
use rhertogh\Yii2Oauth2Server\interfaces\models\Oauth2AccessTokenInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\queries\base\BatchQueryResult\Oauth2AccessTokenBatchQueryResultInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\queries\base\Oauth2BaseActiveQueryInterface;

// phpcs:enable Generic.Files.LineLength.TooLong

interface Oauth2AccessTokenQueryInterface extends Oauth2BaseActiveQueryInterface
{
    /**
     * @inheritDoc
     * @return Oauth2AccessTokenInterface
     */
    public function one($db = null);

    /**
     * @inheritDoc
     * @return Oauth2AccessTokenInterface[]
     */
    public function all($db = null);

    /**
     * @inheritDoc
     * @return Oauth2AccessTokenBatchQueryResultInterface
     */
    public function each($batchSize = 100, $db = null);
}
