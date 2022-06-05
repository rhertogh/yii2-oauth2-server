<?php

namespace rhertogh\Yii2Oauth2Server\interfaces\models\queries;

// phpcs:disable Generic.Files.LineLength.TooLong
use rhertogh\Yii2Oauth2Server\interfaces\models\Oauth2UserClientInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\queries\base\BatchQueryResult\Oauth2UserClientBatchQueryResultInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\queries\base\Oauth2BaseActiveQueryInterface;

// phpcs:enable Generic.Files.LineLength.TooLong

interface Oauth2UserClientQueryInterface extends Oauth2BaseActiveQueryInterface
{
    /**
     * @inheritDoc
     * @return Oauth2UserClientInterface
     */
    public function one($db = null);

    /**
     * @inheritDoc
     * @return Oauth2UserClientInterface[]
     */
    public function all($db = null);

    /**
     * @inheritDoc
     * @return Oauth2UserClientBatchQueryResultInterface
     */
    public function each($batchSize = 100, $db = null);
}
