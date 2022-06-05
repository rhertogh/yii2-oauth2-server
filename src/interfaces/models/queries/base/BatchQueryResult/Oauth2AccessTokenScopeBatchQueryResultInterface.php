<?php

namespace rhertogh\Yii2Oauth2Server\interfaces\models\queries\base\BatchQueryResult;

use rhertogh\Yii2Oauth2Server\interfaces\models\Oauth2AccessTokenInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\Oauth2AccessTokenScopeInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\Oauth2ClientInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\queries\base\BatchQueryResult\base\Oauth2BaseBatchQueryResultInterface;

interface Oauth2AccessTokenScopeBatchQueryResultInterface extends Oauth2BaseBatchQueryResultInterface
{
    /**
     * @inheritdoc
     * @return Oauth2AccessTokenScopeInterface the current dataset.
     */
    public function current();
}
