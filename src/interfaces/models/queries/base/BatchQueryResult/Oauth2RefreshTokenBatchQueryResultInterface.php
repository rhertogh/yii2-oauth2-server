<?php

namespace rhertogh\Yii2Oauth2Server\interfaces\models\queries\base\BatchQueryResult;

use rhertogh\Yii2Oauth2Server\interfaces\models\Oauth2ClientInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\Oauth2ClientScopeInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\Oauth2RefreshTokenInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\queries\base\BatchQueryResult\base\Oauth2BaseBatchQueryResultInterface;

interface Oauth2RefreshTokenBatchQueryResultInterface extends Oauth2BaseBatchQueryResultInterface
{
    /**
     * @inheritdoc
     * @return Oauth2RefreshTokenInterface the current dataset.
     */
    public function current();
}
