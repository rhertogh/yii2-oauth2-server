<?php
namespace Yii2Oauth2ServerTests\unit\models\base;

use rhertogh\Yii2Oauth2Server\models\base\Oauth2BaseActiveRecord;
use Yii2Oauth2ServerTests\unit\TestCase;

/**
 * @covers \rhertogh\Yii2Oauth2Server\models\base\Oauth2BaseActiveRecord
 */
class Oauth2BaseActiveRecordTest extends TestCase
{
    /**
     * @inheritDoc
     */
    public function testFixedTableName()
    {
        $this->mockConsoleApplication();
        $model = new class extends Oauth2BaseActiveRecord {
            public static $tableName = 'custom-table-name';

            public function hasAttribute($name){return false;}
            public function loadDefaultValues($skipIfSet = true){}
        };

        $this->assertEquals('custom-table-name', get_class($model)::tableName());

    }
}
