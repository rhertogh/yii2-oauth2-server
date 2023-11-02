<?php

namespace Yii2Oauth2ServerTests\_helpers\fixtures;

use Yii;
use yii\base\Module;
use yii\console\ExitCode;
use yii\db\Exception;
use yii\helpers\ArrayHelper;
use Yii2Oauth2ServerTests\_helpers\EchoMigrateController;

class DatabaseFixtures
{
    public static $params = null;

    public static function createDbFixtures(
        $driverName,
        $runPreMigrationsFixtures = true,
        $runMigrations = true,
        $runPostMigrationsFixtures = true
    ) {
        $dbConfig = static::getDbConfig($driverName);

        $pdoDriver = 'pdo_' . explode(':', $dbConfig['connection']['dsn'])[0];
//        if ($driverName === 'oci') {
//            $pdoDriver = 'oci8';
//        }

        if (!\extension_loaded('pdo') || !\extension_loaded($pdoDriver)) {
            throw new Exception('pdo and ' . $pdoDriver . ' extension are required.');
        }

        if ($runPreMigrationsFixtures && !empty($dbConfig['preMigrationsFixtures'])) {
            foreach ($dbConfig['preMigrationsFixtures'] as $preMigrationsFixture) {
                static::runFixture($driverName, $preMigrationsFixture);
            }
        }

        if ($runMigrations && !empty($dbConfig['migrations'])) {
            static::runMigrations($dbConfig['migrations']);
        }

        if ($runPostMigrationsFixtures && !empty($dbConfig['postMigrationsFixtures'])) {
            foreach ($dbConfig['postMigrationsFixtures'] as $postMigrationsFixture) {
                static::runFixture($driverName, $postMigrationsFixture);
            }
        }
    }

    protected static function runFixture($driverName, $fixture)
    {
        Yii::$app->db->open();

        // Force committing of any active transactions for PHP < 8
        // due to pdo->inTransaction() bug after implicit commit.
        if (PHP_MAJOR_VERSION < 8) {
            $transaction = Yii::$app->db->beginTransaction();
            try {
                $transaction->commit();
            } catch (\Exception $e) {
            }
            try {
                $transaction->commit();
            } catch (\Exception $e) {
            }
        }

        if ($driverName === 'oci') {
            [$drops, $creates] = explode('/* STATEMENTS */', file_get_contents($fixture), 2);
            [$statements, $triggers, $data] = explode('/* TRIGGERS */', $creates, 3);
            $lines = array_merge(
                explode('--', $drops),
                explode(';', $statements),
                explode('/', $triggers),
                explode(';', $data)
            );
        } else {
            $lines = explode(';', file_get_contents($fixture));
        }
        foreach ($lines as $line) {
            if (trim($line) !== '') {
                try {
                    Yii::$app->db->pdo->query($line)->fetchAll();
                } catch (\Exception $e) {
                    throw new \Exception($e->getMessage() . PHP_EOL . $line, 0, $e);
                }
            }
        }
    }

    /**
     * @param $config
     * @throws \Exception
     */
    protected static function runMigrations($config): void
    {
        $module = new Module('console');

        $migrateController = new EchoMigrateController('migrate', $module, ArrayHelper::merge(
            [
                'migrationPath' => null,
                'interactive' => false,
            ],
            $config
        ));

        try {
            ob_start();
            ob_implicit_flush(false);

            $result = $migrateController->run('up');

            if ($result !== ExitCode::OK) {
                ob_end_flush();
                throw new Exception('Migration(s) failed.');
            }

            ob_end_clean();
        } catch (\Exception $e) {
            ob_end_flush();
            throw $e;
        }
    }

    /**
     * @return mixed
     * @throws \Exception
     */
    public static function getDbConfig($driverName)
    {
        if ($driverName === null) {
            throw new \Exception('driverName is not set for a DatabaseTestCase.');
        }

        $databases = self::getParam('databases');
        $dbConfig = $databases[$driverName];
        return $dbConfig;
    }

    protected static function getParam($name, $default = null)
    {
        if (static::$params === null) {
            static::$params = require(Yii::getAlias('@Yii2Oauth2ServerTests/_data/config.php'));
        }

        return isset(static::$params[$name]) ? static::$params[$name] : $default;
    }
}
