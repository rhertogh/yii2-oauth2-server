<?php

namespace rhertogh\Yii2Oauth2Server\interfaces\models\queries;

// phpcs:disable Generic.Files.LineLength.TooLong
use rhertogh\Yii2Oauth2Server\interfaces\models\Oauth2ClientInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\queries\base\BatchQueryResult\Oauth2ClientBatchQueryResultInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\queries\base\Oauth2BaseActiveQueryInterface;

// phpcs:enable Generic.Files.LineLength.TooLong

interface Oauth2ClientQueryInterface extends Oauth2BaseActiveQueryInterface
{
    /**
     * @inheritDoc
     * @return Oauth2ClientInterface|array|null
     */
    public function one($db = null);

    /**
     * @inheritDoc
     * @return Oauth2ClientInterface[]
     */
    public function all($db = null);

    /**
     * @inheritDoc
     * @return Oauth2ClientBatchQueryResultInterface
     */
    public function each($batchSize = 100, $db = null);
}
