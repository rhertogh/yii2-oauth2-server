<?php

namespace rhertogh\Yii2Oauth2Server\models\queries\base;

use rhertogh\Yii2Oauth2Server\interfaces\models\queries\base\Oauth2BaseActiveQueryInterface;
use yii\db\ActiveQuery;

class Oauth2BaseActiveQuery extends ActiveQuery implements Oauth2BaseActiveQueryInterface
{
    /**
     * @inheritdoc
     */
    public function getVia()
    {
        return $this->via;
    }
}
