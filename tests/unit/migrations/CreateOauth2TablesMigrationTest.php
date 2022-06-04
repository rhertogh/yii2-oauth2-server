<?php

namespace Yii2Oauth2ServerTests\unit\migrations;

use rhertogh\Yii2Oauth2Server\migrations\Oauth2_00001_CreateOauth2TablesMigration;
use rhertogh\Yii2Oauth2Server\Oauth2Module;
use yii\db\TableSchema;
use Yii2Oauth2ServerTests\_helpers\TestUserModel;
use Yii2Oauth2ServerTests\unit\migrations\_base\BaseMigrationTest;

/**
 * @covers \rhertogh\Yii2Oauth2Server\migrations\Oauth2_00001_CreateOauth2TablesMigration
 *
 */
class CreateOauth2TablesMigrationTest extends BaseMigrationTest
{
    public function getMigrationClass()
    {
        return Oauth2_00001_CreateOauth2TablesMigration::class;
    }

    public function dependsOnMigrations()
    {
        return [];
    }

    public function testGenerationIsActive()
    {
        /** @var string|Oauth2_00001_CreateOauth2TablesMigration $migrationClass */
        $migrationClass = $this->getMigrationClassWrapper($this->getMigrationClass());
        $this->assertTrue($migrationClass::generationIsActive(Oauth2Module::getInstance()));
    }

    public function testGetTablesWithoutModule()
    {
        $this->mockApplicationWithoutOauth2();

        $this->expectExceptionMessage(
            'Oauth2Module is not instantiated. Is it added to the config in the "module" and "bootstrap" section?'
        );
        $this->getMockMigration()->getTablesWrapper();
    }

    public function testUserModelWithMultiColumnPk()
    {
        $this->mockConsoleApplication([
            'modules' => [
                'oauth2' => [
                    'identityClass' => get_class(new class extends TestUserModel {
                        public static function getTableSchema()
                        {
                            return new TableSchema([
                                'primaryKey' => ['column_a', 'column_b'],
                            ]);
                        }
                    }),
                ],
            ],
        ]);

        $this->expectExceptionMessage('The primary key of `userClass` must consist of a single column');
        $this->getMockMigration()->getTablesWrapper();
    }

    public function testMigrationWithoutUserTable()
    {
        $this->mockConsoleApplication([
            'modules' => [
                'oauth2' => [
                    'identityClass' => get_class(new class extends TestUserModel {
                        public static function getTableSchema()
                        {
                            return false;
                        }
                    }),
                ],
            ],
        ]);

        $this->runDependentMigration();
        $migrationClass = $this->getMigrationClassWrapper($this->getMigrationClass());
        $this->assertNotFalse($this->runMigration($migrationClass, static::MIGRATE_UP));
        $this->assertNotFalse($this->runMigration($migrationClass, static::MIGRATE_DOWN));
    }

    public function testNumTables()
    {
        $migration = $this->getMockMigration();
        $this->setInaccessibleProperty($migration, 'numTables', 9999);

        $this->expectExceptionMessage(
            'Incorrect number of tables in definition. Are the Active Record classes correctly configured?'
        );
        $migration->getTablesWrapper();
    }

    /**
     * @return Oauth2_00001_CreateOauth2TablesMigration
     */
    protected function getMockMigration()
    {
        return new class extends Oauth2_00001_CreateOauth2TablesMigration {
            public function getTablesWrapper()
            {
                return $this->getTables();
            }
        };
    }
}
