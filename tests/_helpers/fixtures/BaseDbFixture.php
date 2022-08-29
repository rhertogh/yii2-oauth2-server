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
        $this->db = new Connection(DatabaseFixtures::getDbConfig($this->driverName)['connection']);
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
