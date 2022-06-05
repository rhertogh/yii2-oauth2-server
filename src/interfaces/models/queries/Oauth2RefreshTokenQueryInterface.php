<?php

namespace rhertogh\Yii2Oauth2Server\interfaces\models\queries;

// phpcs:disable Generic.Files.LineLength.TooLong
use rhertogh\Yii2Oauth2Server\interfaces\models\Oauth2RefreshTokenInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\queries\base\BatchQueryResult\Oauth2RefreshTokenBatchQueryResultInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\queries\base\Oauth2BaseActiveQueryInterface;

// phpcs:enable Generic.Files.LineLength.TooLong

interface Oauth2RefreshTokenQueryInterface extends Oauth2BaseActiveQueryInterface
{
    /**
     * @inheritDoc
     * @return Oauth2RefreshTokenInterface|array|null
     */
    public function one($db = null);

    /**
     * @inheritDoc
     * @return Oauth2RefreshTokenInterface[]
     */
    public function all($db = null);

    /**
     * @inheritDoc
     * @return Oauth2RefreshTokenBatchQueryResultInterface
     */
    public function each($batchSize = 100, $db = null);
}
