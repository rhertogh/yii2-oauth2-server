<?php

namespace rhertogh\Yii2Oauth2Server\interfaces\models\queries\base\BatchQueryResult;

use rhertogh\Yii2Oauth2Server\interfaces\models\Oauth2AuthCodeScopeInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\Oauth2ClientInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\queries\base\BatchQueryResult\base\Oauth2BaseBatchQueryResultInterface;

interface Oauth2AuthCodeScopeBatchQueryResultInterface extends Oauth2BaseBatchQueryResultInterface
{
    /**
     * @inheritdoc
     * @return Oauth2AuthCodeScopeInterface the current dataset.
     */
    public function current();
}
