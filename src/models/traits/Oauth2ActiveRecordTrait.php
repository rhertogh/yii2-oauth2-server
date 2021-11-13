<?php

namespace rhertogh\Yii2Oauth2Server\models\traits;

use rhertogh\Yii2Oauth2Server\interfaces\models\base\Oauth2ActiveRecordInterface;
use Yii;
use yii\db\ActiveRecord;
use yii\db\Exception as DbException;
use yii\helpers\ArrayHelper;

trait Oauth2ActiveRecordTrait
{
    /**
     * @inheritDoc
     */
    public static function findOrCreate($condition)
    {
        $activeRecord = static::findOne($condition);

        if (empty($activeRecord)) {
            $activeRecord = Yii::createObject(ArrayHelper::merge($condition, [
                'class' => static::class,
            ]));
        }

        return $activeRecord;
    }

    /**
     * @inheritDoc
     */
    public function persist($runValidation = true, $attributeNames = null)
    {
        /** @var ActiveRecord|Oauth2ActiveRecordInterface $this */
        if (!$this->save($runValidation, $attributeNames)) {
            throw new DbException('Could not save ' . static::class .
                (YII_DEBUG ? PHP_EOL . print_r($this->attributes, true) : '') .
                ' Errors: ' . PHP_EOL . implode(', ', $this->getErrorSummary(true)));
        }

        return $this;
    }
}
