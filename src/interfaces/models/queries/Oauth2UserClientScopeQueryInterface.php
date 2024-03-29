<?php

namespace rhertogh\Yii2Oauth2Server\interfaces\models\queries;

// phpcs:disable Generic.Files.LineLength.TooLong
use rhertogh\Yii2Oauth2Server\interfaces\models\Oauth2UserClientScopeInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\queries\base\BatchQueryResult\Oauth2UserClientScopeBatchQueryResultInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\queries\base\Oauth2BaseActiveQueryInterface;

// phpcs:enable Generic.Files.LineLength.TooLong

interface Oauth2UserClientScopeQueryInterface extends Oauth2BaseActiveQueryInterface
{
    /**
     * @inheritDoc
     * @return Oauth2UserClientScopeInterface|array|null
     */
    public function one($db = null);

    /**
     * @inheritDoc
     * @return Oauth2UserClientScopeInterface[]
     */
    public function all($db = null);

    /**
     * @inheritDoc
     * @return Oauth2UserClientScopeBatchQueryResultInterface
     */
    public function each($batchSize = 100, $db = null);
}
