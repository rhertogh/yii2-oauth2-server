<?php

namespace Yii2Oauth2ServerTests\unit\models\traits;

use rhertogh\Yii2Oauth2Server\models\traits\Oauth2ActiveRecordTrait;
use yii\base\InvalidArgumentException;
use Yii2Oauth2ServerTests\unit\TestCase;

/**
 * @covers \rhertogh\Yii2Oauth2Server\models\traits\Oauth2ActiveRecordTrait
 */
class Oauth2ActiveRecordTraitTest extends TestCase
{
    public function testFindByPkEmptyPk()
    {
        /** @var Oauth2ActiveRecordTrait|class-string $modelClass */
        $modelClass = get_class(new class {
            use Oauth2ActiveRecordTrait;
        });

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('$pk can not be empty.');
        $modelClass::findByPk([]);
    }

    public function testFindOrCreate()
    {
        /** @var Oauth2ActiveRecordTrait|class-string $modelClass */
        $modelClass = get_class(new class {
            use Oauth2ActiveRecordTrait;

            public $id = null;

            public static function findOne($condition)
            {
                return null;
            }
        });

        $model = $modelClass::findOrCreate(['id' => 1]);
        $this->assertEquals(1, $model->id);
    }
}
