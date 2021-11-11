<?php

namespace rhertogh\Yii2Oauth2Server\models\queries\traits;

use rhertogh\Yii2Oauth2Server\interfaces\models\base\Oauth2ActiveRecordInterface;
use yii\db\ActiveQuery;

trait Oauth2EnabledQueryTrait
{
    /**
     * @inheritDoc
     */
    public function enabled($enabled = true)
    {
        /** @var ActiveQuery $this */
        if (!is_null($enabled)) {
            if (is_null($this->from)){
                /** @var Oauth2ActiveRecordInterface $modelClass */
                $modelClass = $this->modelClass;
                $table = $modelClass::tableName();
            } else {
                $table = array_key_first($this->from);
            }
            $this->andWhere([ "$table.enabled" => $enabled]);
        }

        return $this;
    }
}
