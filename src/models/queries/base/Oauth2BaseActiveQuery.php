<?php

namespace rhertogh\Yii2Oauth2Server\models\queries\base;

use yii\db\ActiveQuery;

class Oauth2BaseActiveQuery extends ActiveQuery
{
    /**
     * @inheritdoc
     * @return static[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return static|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
