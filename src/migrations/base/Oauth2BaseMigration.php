<?php

namespace rhertogh\Yii2Oauth2Server\migrations\base;

use rhertogh\Yii2Oauth2Server\helpers\DiHelper;
use rhertogh\Yii2Oauth2Server\Oauth2Module;
use yii\base\InvalidConfigException;
use yii\db\ColumnSchema;
use yii\db\ColumnSchemaBuilder;
use yii\db\Migration;
use yii\db\Schema;
use yii\db\TableSchema;

abstract class Oauth2BaseMigration extends Migration
{
    public const RESTRICT = 'RESTRICT';
    public const CASCADE = 'CASCADE';

    /**
     * Determines if the migration should be generated for the current module configuration.
     * @param Oauth2Module $module
     * @return bool
     * @since 1.0.0
     */
    abstract public static function generationIsActive($module);

    /**
     * Get the table name for a model.
     * @param string $tableClass
     * @return string
     * @throws InvalidConfigException
     * @since 1.0.0
     */
    protected function getTableName($tableClass)
    {
        return call_user_func([DiHelper::getValidatedClassName($tableClass), 'tableName']);
    }

    /**
     * Get the table schema for a model.
     * @param string $tableClass
     * @return TableSchema
     * @throws InvalidConfigException
     * @since 1.0.0
     */
    protected function getTableSchema($tableClass)
    {
        return call_user_func([DiHelper::getValidatedClassName($tableClass), 'getTableSchema']);
    }

    /**
     * @param ColumnSchema $columnSchema
     * @return ColumnSchemaBuilder
     */
    protected function getColumnSchemaBuilder($columnSchema): ColumnSchemaBuilder
    {
        $typeFunction = str_replace(
            [
                Schema::TYPE_TINYINT,
                Schema::TYPE_SMALLINT,
                Schema::TYPE_BIGINT,
            ],
            [
                'tinyInteger',
                'smallInteger',
                'bigInteger',
            ],
            $columnSchema->type
        );
        return $this->{$typeFunction}();
    }
}
