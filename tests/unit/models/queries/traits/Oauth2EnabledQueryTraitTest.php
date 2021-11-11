<?php

namespace Yii2Oauth2ServerTests\unit\models\queries\traits;

use rhertogh\Yii2Oauth2Server\models\queries\traits\Oauth2EnabledQueryTrait;
use yii\db\Query;
use Yii2Oauth2ServerTests\unit\TestCase;

/**
 * @covers \rhertogh\Yii2Oauth2Server\models\queries\traits\Oauth2EnabledQueryTrait
 */
class Oauth2EnabledQueryTraitTest extends TestCase
{
    public function testEnabled()
    {
        $query = new class extends Query {
            public $modelClass = null;
            use Oauth2EnabledQueryTrait;
        };

        $query->modelClass = get_class(new class {
            public static function tableName()
            {
                return 'test';
            }
        });

        $query->enabled(true);
        $this->assertEquals(['test.enabled' => true], $query->where);
    }

    public function testEnabledFrom()
    {
        $query = new class extends Query {
            public $modelClass = null;
            use Oauth2EnabledQueryTrait;
        };

        $query->from = ['test_alias' => 'test'];

        $query->enabled(true);
        $this->assertEquals(['test_alias.enabled' => true], $query->where);
    }
}
