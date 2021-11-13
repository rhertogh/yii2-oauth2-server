<?php

namespace Yii2Oauth2ServerTests\unit\models\_base;

use rhertogh\Yii2Oauth2Server\interfaces\models\base\Oauth2ActiveRecordInterface;
use Yii;
use yii\db\ActiveRecord;
use yii\db\Exception as DbException;
use yii\helpers\ArrayHelper;
use Yii2Oauth2ServerTests\unit\DatabaseTestCase;
use Yii2Oauth2ServerTests\unit\models\_traits\_base\Oauth2BaseModelTestTrait;

abstract class BaseOauth2ActiveRecordTest extends DatabaseTestCase
{
    use Oauth2BaseModelTestTrait;

    /**
     * @return array[]
     */
    abstract public function persistTestProvider();

    /**
     * @param array $modelData
     * @param bool $expectSuccess
     *
     * @dataProvider persistTestProvider
     */
    public function testPersist($modelData, $expectSuccess, $beforePersist = null)
    {
        if (!$expectSuccess) {
            $this->expectException(DbException::class);
        }

        $model = $this->getMockModel($modelData);

        if ($beforePersist) {
            call_user_func($beforePersist, $model);
        }

        $model->persist();
        $model->refresh();

        $this->assertNotEmpty($model);
        $this->assertNotEmpty($model->getPrimaryKey());
    }

    /**
     * @param array $config
     * @return Oauth2ActiveRecordInterface|ActiveRecord
     * @throws \yii\base\InvalidConfigException
     */
    protected function getMockModel($config = [])
    {
        return Yii::createObject(ArrayHelper::merge(['class' => $this->getModelInterface()], $config));
    }
}
