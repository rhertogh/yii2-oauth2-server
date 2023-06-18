<?php

namespace rhertogh\Yii2Oauth2Server\models\traits;

use rhertogh\Yii2Oauth2Server\interfaces\models\base\Oauth2ActiveRecordInterface;
use Yii;
use yii\base\InvalidArgumentException;
use yii\base\InvalidConfigException;
use yii\db\ActiveRecord;
use yii\db\Exception as DbException;
use yii\db\TableSchema;
use yii\helpers\ArrayHelper;

trait Oauth2ActiveRecordTrait
{
    /**
     * @param int|string|int[]|string[] $pk
     * @return static
     */
    public static function findByPk($pk)
    {
        if (empty($pk)) {
            throw new InvalidArgumentException('$pk can not be empty.');
        }
        /** @var TableSchema $tableSchema */
        $tableSchema = static::getTableSchema();
        $numPkColumns = count($tableSchema->primaryKey);
        if ($numPkColumns === 1) {
            $primaryKey = [$tableSchema->primaryKey[0] => (is_array($pk) ? reset($pk) : $pk)];
        } elseif ($numPkColumns > 1) {
            $primaryKey = [];
            foreach ($tableSchema->primaryKey as $column) {
                if (empty($pk[$column])) {
                    throw new InvalidArgumentException('$pk[' . $column . '] can not be empty.');
                }
                $primaryKey[$column] = $pk[$column];
            }
        } else {
            throw new InvalidConfigException(static::class . ' is missing a primary key.');
        }

        return static::findOne($primaryKey);
    }

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
