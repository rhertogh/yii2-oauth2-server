<?php

namespace rhertogh\Yii2Oauth2Server\interfaces\models\queries\base\BatchQueryResult\base;

use rhertogh\Yii2Oauth2Server\interfaces\models\base\Oauth2ActiveRecordInterface;

interface Oauth2BaseBatchQueryResultInterface extends \Iterator
{
    /**
     * Returns the current dataset.
     * This method is required by the interface [[\Iterator]].
     * @return Oauth2ActiveRecordInterface the current dataset.
     * @since 1.0.0
     */
    public function current();
}
