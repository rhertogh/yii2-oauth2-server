<?php

namespace Yii2Oauth2ServerTests\unit\migrations\base;

use rhertogh\Yii2Oauth2Server\migrations\base\Oauth2BaseMigration;
use yii\db\ColumnSchema;
use yii\db\ColumnSchemaBuilder;
use yii\db\Schema;
use yii\db\TableSchema;
use Yii2Oauth2ServerTests\unit\DatabaseTestCase;

/**
 * @covers \rhertogh\Yii2Oauth2Server\migrations\base\Oauth2BaseMigration
 *
 */
class Oauth2BaseMigrationTest extends DatabaseTestCase
{
    public function testGetTableName()
    {
        $mockClass = get_class(new class {
            public static function tableName()
            {
                return 'test-table-name';
            }
        });

        $migration = new class extends Oauth2BaseMigration {
            public static function generationIsActive($module)
            {
            }

            public function pubGetTableName($tableClass)
            {
                return $this->getTableName($tableClass);
            }
        };

        $this->assertEquals('test-table-name', $migration->pubGetTableName($mockClass));
    }

    public function testGetTableSchema()
    {
        $mockClass = get_class(new class {
            public static function getTableSchema()
            {
                return new TableSchema();
            }
        });

        $migration = new class extends Oauth2BaseMigration {
            public static function generationIsActive($module)
            {
            }
            public function pubGetTableSchema($tableClass)
            {
                return $this->getTableSchema($tableClass);
            }
        };

        $this->assertInstanceOf(TableSchema::class, $migration->pubGetTableSchema($mockClass));
    }

    /**
     * @param string $schemaType
     * @return void
     *
     * @dataProvider getColumnSchemaBuilderDataProvider
     */
    public function testGetColumnSchemaBuilder($schemaType)
    {
        $migration = $this->getMockMigration();
        /** @var ColumnSchemaBuilder $columnSchemaBuilder */
        $columnSchemaBuilder = $this->callInaccessibleMethod(
            $migration,
            'getColumnSchemaBuilder',
            [new ColumnSchema(['type' => $schemaType])]
        );

        $this->assertInstanceOf(ColumnSchemaBuilder::class, $columnSchemaBuilder);
    }

    /**
     * @return array[]
     * @see testGetColumnSchemaBuilder
     */
    public function getColumnSchemaBuilderDataProvider()
    {
        return [
            [Schema::TYPE_CHAR],
            [Schema::TYPE_STRING],
            [Schema::TYPE_TINYINT],
            [Schema::TYPE_SMALLINT],
            [Schema::TYPE_INTEGER],
            [Schema::TYPE_BIGINT],
            [Schema::TYPE_FLOAT],
            [Schema::TYPE_DOUBLE],
            [Schema::TYPE_DECIMAL],
        ];
    }

    /**
     * @return Oauth2BaseMigration
     */
    protected function getMockMigration()
    {
        return new class extends Oauth2BaseMigration {

            public static function generationIsActive($module)
            {
                return false;
            }
            public function getTablesWrapper()
            {
                return $this->getTables();
            }
        };
    }
}
