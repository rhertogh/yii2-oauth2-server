<?php

namespace Yii2Oauth2ServerTests\_helpers\fixtures;

use Yii;
use yii\db\Connection;

abstract class BaseDbFixture extends \yii\test\InitDbFixture
{
    public $driverName = 'mysql';

    protected static $_created = false;

    public function init()
    {
        $connectionConfig = DatabaseFixtures::getDbConfig($this->driverName)['connection'];
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
