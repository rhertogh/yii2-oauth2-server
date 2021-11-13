<?php

namespace Yii2Oauth2ServerTests\unit\migrations\_base;

use rhertogh\Yii2Oauth2Server\interfaces\models\Oauth2ScopeInterface;
use rhertogh\Yii2Oauth2Server\migrations\base\Oauth2BaseMigration;
use Yii;
use yii\db\ActiveRecord;
use yii\db\Migration;
use yii\helpers\StringHelper;
use Yii2Oauth2ServerTests\unit\DatabaseTestCase;
use Yii2Oauth2ServerTests\unit\models\_base\BaseOauth2ActiveRecordTest;
use Yii2Oauth2ServerTests\unit\models\_traits\Oauth2IdentifierTestTrait;
use Yii2Oauth2ServerTests\unit\models\_traits\Oauth2IdTestTrait;

abstract class BaseMigrationTest extends DatabaseTestCase
{
    protected const MIGRATE_UP = 'Up';
    protected const MIGRATE_DOWN = 'Down';

    protected $runMigrations = false;
    protected $runPostMigrationsFixtures = false;

    abstract public function getMigrationClass();

    /**
     * @return string|Oauth2BaseMigration
     */
    abstract public function dependsOnMigrations();

    public function getMigrationClassWrapper($migrationClass)
    {
        $migrationClassName = StringHelper::basename($migrationClass);
        $wrapperName = $migrationClassName . 'Wrapper';
        $wrapperClass = __NAMESPACE__ . '\\' . $wrapperName;
        if (!class_exists($wrapperClass, false)) {
            eval('namespace ' . __NAMESPACE__ . '; class ' . $wrapperName . ' extends \\' . $migrationClass . ' {}');
        }
        return $wrapperClass;
    }

    /**
     *
     */
    public function testSaveUpDown()
    {
        $this->runDependentMigration();
        $migrationClass = $this->getMigrationClassWrapper($this->getMigrationClass());
        $this->assertNotFalse($this->runMigration($migrationClass, static::MIGRATE_UP));
        $this->assertNotFalse($this->runMigration($migrationClass, static::MIGRATE_DOWN));
    }

    protected function runDependentMigration()
    {
        $migrations = $this->dependsOnMigrations();

        foreach ($migrations as $migrationClass) {
            $this->runMigration($this->getMigrationClassWrapper($migrationClass), static::MIGRATE_UP);
        }
    }

    protected function runMigration($migrationClass, $direction)
    {
        /** @var Migration $migration */
        $migration = Yii::createObject([
            'class' => $migrationClass,
            'db' => Yii::$app->db,
            'compact' => true,
        ]);

        return $migration->{'safe' . $direction}();
    }
}
