<?php

namespace rhertogh\Yii2Oauth2Server\models\base;

use rhertogh\Yii2Oauth2Server\interfaces\models\base\Oauth2ActiveRecordInterface;
use rhertogh\Yii2Oauth2Server\models\behaviors\BooleanBehavior;
use rhertogh\Yii2Oauth2Server\models\behaviors\TimestampBehavior;
use rhertogh\Yii2Oauth2Server\models\traits\Oauth2ActiveRecordTrait;
use yii\db\ActiveRecord;
use yii\helpers\Inflector;
use yii\helpers\StringHelper;

abstract class Oauth2BaseActiveRecord extends ActiveRecord implements Oauth2ActiveRecordInterface
{
    use Oauth2ActiveRecordTrait;

    /**
     * @var string|null The name for the table
     */
    public static $tableName = null;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        if (!empty(static::$tableName)) {
            return static::$tableName;
        }

        return '{{%' . Inflector::camel2id(StringHelper::basename(get_called_class()), '_') . '}}';
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'timestampBehavior' => [
                'class' => TimestampBehavior::class,
                'createdAtAttribute' => $this->hasAttribute('created_at') ? 'created_at' : false,
                'updatedAtAttribute' => $this->hasAttribute('updated_at') ? 'updated_at' : false,
            ],
            'booleanBehavior' => BooleanBehavior::class,
        ];
    }

    public function init()
    {
        parent::init();
        $this->loadDefaultValues();
    }
}
