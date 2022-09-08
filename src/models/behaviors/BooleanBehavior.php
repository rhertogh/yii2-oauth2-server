<?php

namespace rhertogh\Yii2Oauth2Server\models\behaviors;

use yii\base\Behavior;
use yii\db\ActiveRecord;
use yii\db\Schema;

class BooleanBehavior extends Behavior
{
    /**
     * Nummeric database schema types.
     */
    public const INTEGER_SCHEMA_TYPES = [
        Schema::TYPE_INTEGER,
        Schema::TYPE_BIGINT,
        Schema::TYPE_SMALLINT,
        Schema::TYPE_TINYINT,
    ];

    /**
     * @inheritDoc
     */
    public function events()
    {
        return [
            ActiveRecord::EVENT_BEFORE_INSERT => 'boolToInt',
            ActiveRecord::EVENT_BEFORE_UPDATE => 'boolToInt',
            ActiveRecord::EVENT_AFTER_INSERT => 'intToBool',
            ActiveRecord::EVENT_AFTER_UPDATE => 'intToBool',
            ActiveRecord::EVENT_AFTER_FIND => 'intToBool',
        ];
    }

    /**
     * Convert boolean values to integers before inserting them into the database.
     * @throws \yii\base\InvalidConfigException
     * @since 1.0.0
     */
    public function boolToInt()
    {
        /** @var ActiveRecord $activeRecord */
        $activeRecord = $this->owner;

        foreach ($activeRecord->getTableSchema()->columns as $column) {
            if (is_bool($activeRecord->{$column->name}) && in_array($column->type, static::INTEGER_SCHEMA_TYPES)) {
                $activeRecord->{$column->name} = $activeRecord->{$column->name} ? 1 : 0;
            }
        }
    }

    /**
     * Convert integer values to booleans after loading them from the database.
     * @throws \yii\base\InvalidConfigException
     * @since 1.0.0
     */
    public function intToBool()
    {
        /** @var ActiveRecord $activeRecord */
        $activeRecord = $this->owner;

        foreach ($activeRecord->getTableSchema()->columns as $column) {
            if (
                is_int($activeRecord->{$column->name})
                && $column->size === 1
            ) {
                $activeRecord->{$column->name} = (bool)$activeRecord->{$column->name};
            }
        }
    }
}
