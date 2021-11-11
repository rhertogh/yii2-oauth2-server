<?php

namespace Yii2Oauth2ServerTests\unit\models\_traits\_base;

use rhertogh\Yii2Oauth2Server\interfaces\models\base\Oauth2ActiveRecordInterface;
use yii\db\ActiveRecord;

trait Oauth2BaseModelTestTrait
{
    /**
     * @return Oauth2ActiveRecordInterface|string
     */
    abstract protected function getModelInterface();

    /**
     * @param array $config
     * @return Oauth2ActiveRecordInterface|ActiveRecord
     * @throws \yii\base\InvalidConfigException
     */
    abstract protected function getMockModel($config = []);
}
