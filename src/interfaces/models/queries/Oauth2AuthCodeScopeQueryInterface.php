<?php

namespace rhertogh\Yii2Oauth2Server\interfaces\models\queries;

// phpcs:disable Generic.Files.LineLength.TooLong
use rhertogh\Yii2Oauth2Server\interfaces\models\Oauth2AuthCodeScopeInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\queries\base\BatchQueryResult\Oauth2AuthCodeScopeBatchQueryResultInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\queries\base\Oauth2BaseActiveQueryInterface;

// phpcs:enable Generic.Files.LineLength.TooLong

interface Oauth2AuthCodeScopeQueryInterface extends Oauth2BaseActiveQueryInterface
{
    /**
     * @inheritDoc
     * @return Oauth2AuthCodeScopeInterface|array|null
     */
    public function one($db = null);

    /**
     * @inheritDoc
     * @return Oauth2AuthCodeScopeInterface[]
     */
    public function all($db = null);

    /**
     * @inheritDoc
     * @return Oauth2AuthCodeScopeBatchQueryResultInterface
     */
    public function each($batchSize = 100, $db = null);
}
