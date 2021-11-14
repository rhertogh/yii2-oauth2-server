<?php

namespace Yii2Oauth2ServerTests\unit\models;

use rhertogh\Yii2Oauth2Server\interfaces\models\Oauth2AuthCodeScopeInterface;
use yii\db\ActiveRecord;
use Yii2Oauth2ServerTests\unit\models\_base\BaseOauth2ActiveRecordTest;

/**
 * @covers \rhertogh\Yii2Oauth2Server\models\Oauth2AuthCodeScope
 * @covers \rhertogh\Yii2Oauth2Server\models\base\Oauth2AuthCodeScope
 * @covers \rhertogh\Yii2Oauth2Server\models\base\Oauth2BaseActiveRecord
 * @covers \rhertogh\Yii2Oauth2Server\models\queries\Oauth2AuthCodeScopeQuery
 * @covers \rhertogh\Yii2Oauth2Server\models\queries\base\Oauth2BaseActiveQuery
 * @covers \rhertogh\Yii2Oauth2Server\models\behaviors\BooleanBehavior
 * @covers \rhertogh\Yii2Oauth2Server\models\behaviors\DateTimeBehavior
 * @covers \rhertogh\Yii2Oauth2Server\models\behaviors\TimestampBehavior
 *
 * @method Oauth2AuthCodeScopeInterface|ActiveRecord getMockModel(array $config = [])
 */
class Oauth2AuthCodeScopeTest extends BaseOauth2ActiveRecordTest
{
    /**
     * @return Oauth2AuthCodeScopeInterface|string
     */
    protected function getModelInterface()
    {
        return Oauth2AuthCodeScopeInterface::class;
    }

    /**
     * @return array[]
     * @see BaseOauth2ActiveRecordTest::testPersist()
     */
    public function persistTestProvider()
    {
        $this->mockConsoleApplication();
        return [
            // Valid.
            [
                [
                    'auth_code_id' => 1002000,
                    'scope_id' => 1005001,
                ],
                true,
            ],
            // Invalid (non-existing access token).
            [
                [
                    'auth_code_id' => 999999,
                    'scope_id' => 1005001,
                ],
                false,
            ],
            // Invalid (non-existing scope).
            [
                [
                    'auth_code_id' => 1002000,
                    'scope_id' => 999999,
                ],
                false,
            ],
        ];
    }
}
