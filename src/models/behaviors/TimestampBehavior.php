<?php
/**
 * Created by PhpStorm.
 * User: Nick van der Meij
 * Date: 4-5-2016
 * Time: 10:28
 */

namespace rhertogh\Yii2Oauth2Server\models\behaviors;


use yii\db\BaseActiveRecord;

class TimestampBehavior extends \yii\behaviors\TimestampBehavior
{
    /**
     * @inheritdoc
     */
    public function init()
    {
        if (empty($this->attributes)) {
            $this->attributes = [
                BaseActiveRecord::EVENT_INIT => [$this->createdAtAttribute, $this->updatedAtAttribute],
                BaseActiveRecord::EVENT_BEFORE_UPDATE => $this->updatedAtAttribute,
            ];
        }

        parent::init();
    }
}
