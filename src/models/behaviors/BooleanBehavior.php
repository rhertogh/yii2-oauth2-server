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
    public const NUMERIC_SCHEMA_TYPES = [
        Schema::TYPE_INTEGER,
        Schema::TYPE_BIGINT,
        Schema::TYPE_SMALLINT,
        Schema::TYPE_TINYINT,
        Schema::TYPE_FLOAT,
    ];

    /**
     * @inheritDoc
     */
    public function events()
    {
        return [
            ActiveRecord::EVENT_BEFORE_VALIDATE => 'boolToInt',
            ActiveRecord::EVENT_BEFORE_INSERT => 'boolToInt',
            ActiveRecord::EVENT_BEFORE_UPDATE => 'boolToInt',
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
            if (is_bool($activeRecord->{$column->name}) && in_array($column->type, static::NUMERIC_SCHEMA_TYPES)) {
                $activeRecord->{$column->name} = $activeRecord->{$column->name} ? 1 : 0;
            }
        }
    }
}
