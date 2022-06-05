<?php

namespace rhertogh\Yii2Oauth2Server\interfaces\models\queries\base\BatchQueryResult;

use rhertogh\Yii2Oauth2Server\interfaces\models\Oauth2AuthCodeInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\queries\base\BatchQueryResult\base\Oauth2BaseBatchQueryResultInterface;

interface Oauth2AuthCodeBatchQueryResultInterface extends Oauth2BaseBatchQueryResultInterface
{
    /**
     * @inheritdoc
     * @return Oauth2AuthCodeInterface the current dataset.
     */
    public function current();
}
