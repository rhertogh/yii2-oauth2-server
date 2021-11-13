<?php

/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace Yii2Oauth2ServerTests\unit;

use Yii;
use yii\helpers\ArrayHelper;
use Yii2Oauth2ServerTests\_helpers\DatabaseFixtures;

abstract class DatabaseTestCase extends TestCase
{
    protected $runPreMigrationsFixtures = true;
    protected $runMigrations = true;
    protected $runPostMigrationsFixtures = true;

    /**
     * @var string the driver name of this test class. Must be set by a subclass.
     */
    protected $driverName = 'mysql';

    /**
     * @param array $config
     * @return array
     */
    protected function getMockBaseAppConfig($config = []): array
    {
        $dbConfig = DatabaseFixtures::getDbConfig($this->driverName);
        return ArrayHelper::merge(
            parent::getMockBaseAppConfig(),
            [
                'components' => [
                    'db' => [
                        'class' => $dbConfig['class'] ?? 'yii\db\Connection',
                        'dsn' => $dbConfig['dsn'],
                        'username' => $dbConfig['username'] ?? null,
                        'password' => $dbConfig['password'] ?? null,
                    ],
                ],
            ],
            $config,
        );
    }

    protected function _before()
    {
        parent::_before();
        static::mockConsoleApplication();
        DatabaseFixtures::createDbFixtures(
            $this->driverName,
            $this->runPreMigrationsFixtures,
            $this->runMigrations,
            $this->runPostMigrationsFixtures
        );
    }

    protected function _after()
    {
        if (Yii::$app->db) {
            Yii::$app->db->close();
        }

        parent::_after();
    }
}
