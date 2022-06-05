<?php

namespace rhertogh\Yii2Oauth2Server\interfaces\models\queries;

// phpcs:disable Generic.Files.LineLength.TooLong
use rhertogh\Yii2Oauth2Server\interfaces\models\Oauth2AuthCodeInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\queries\base\BatchQueryResult\Oauth2AuthCodeBatchQueryResultInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\queries\base\Oauth2BaseActiveQueryInterface;

// phpcs:enable Generic.Files.LineLength.TooLong

interface Oauth2AuthCodeQueryInterface extends Oauth2BaseActiveQueryInterface
{
    /**
     * @inheritDoc
     * @return Oauth2AuthCodeInterface
     */
    public function one($db = null);

    /**
     * @inheritDoc
     * @return Oauth2AuthCodeInterface[]
     */
    public function all($db = null);

    /**
     * @inheritDoc
     * @return Oauth2AuthCodeBatchQueryResultInterface
     */
    public function each($batchSize = 100, $db = null);
}
