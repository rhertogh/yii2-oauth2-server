<?php

namespace Yii2Oauth2ServerTests\_helpers\fixtures;

use Yii;
use yii\db\Connection;
use yii\helpers\ArrayHelper;

abstract class BaseDbFixture extends \yii\test\InitDbFixture
{
    public $driverName = null;

    protected static $_created = false;

    public function init()
    {
        $this->driverName = $this->driverName ?: getenv('YII2_OAUTH2_SERVER_TEST_DB_DRIVER') ?: 'MySQL';
        $connectionConfig = ArrayHelper::merge(
            require __DIR__ . '/../../_config/db.php',
            DatabaseFixtures::getDbConfig($this->driverName)['connection'],
        );
        $this->db = new Connection($connectionConfig);
        Yii::$app->setComponents([
            'db' => $this->db,
        ]);
        parent::init();
    }

    public function load()
    {
        if (static::$_created !== static::class) {
            $this->createDbFixtures();
            static::$_created = static::class;
            $this->db->getSchema()->refresh();
        }
    }

    abstract protected function createDbFixtures();

//    public function afterLoad()
//    {
//        parent::afterLoad();
//        Yii::$app->setComponents([
//            'db' => $this->db,
//        ]);
//    }
}
