<?php

namespace rhertogh\Yii2Oauth2Server\models\behaviors;

use DateTimeImmutable;
use yii\base\Behavior;
use yii\db\ActiveRecord;
use yii\db\Schema;

class DateTimeBehavior extends Behavior
{
    /**
     * The DateTime format to convert to before inserting the DateTime object into the database.
     * @var string
     */
    public $dateTimeFormat = 'Y-m-d H:i:s';

    /**
     * @inheritDoc
     */
    public function events()
    {
        return [
            ActiveRecord::EVENT_BEFORE_INSERT => 'beforeSave',
            ActiveRecord::EVENT_BEFORE_UPDATE => 'beforeSave',
            ActiveRecord::EVENT_AFTER_INSERT => 'afterFind',
            ActiveRecord::EVENT_AFTER_UPDATE => 'afterFind',
            ActiveRecord::EVENT_AFTER_FIND => 'afterFind',
        ];
    }

    /**
     * Convert DateTime objects to string before writing the database.
     * @throws \yii\base\InvalidConfigException
     * @since 1.0.0
     */
    public function beforeSave()
    {
        /** @var ActiveRecord $activeRecord */
        $activeRecord = $this->owner;

        foreach ($activeRecord->getTableSchema()->columns as $column) {
            $value = $this->owner[$column->name];
            if (
                $column->type === Schema::TYPE_DATETIME
                && ($value instanceof \DateTime || $value instanceof DateTimeImmutable)
            ) {
                $this->owner[$column->name] = $value->format($this->dateTimeFormat);
            }
        }
    }

    /**
     * Convert strings to DateTime objects after loading them from the database.
     * @throws \yii\base\InvalidConfigException
     * @since 1.0.0
     */
    public function afterFind()
    {
        /** @var ActiveRecord $activeRecord */
        $activeRecord = $this->owner;

        foreach ($activeRecord->getTableSchema()->columns as $column) {
            $value = $this->owner[$column->name];
            if ($column->type === Schema::TYPE_DATETIME && !empty($value)) {
                $dateTime = DateTimeImmutable::createFromFormat($this->dateTimeFormat, $value);
                $this->owner[$column->name] = $dateTime;
                $this->owner->setOldAttribute($column->name, $dateTime);
            }
        }
    }
}
