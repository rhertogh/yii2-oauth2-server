<?php

namespace Yii2Oauth2ServerTests\unit\models;

use rhertogh\Yii2Oauth2Server\interfaces\models\Oauth2AccessTokenScopeInterface;
use yii\db\ActiveRecord;
use Yii2Oauth2ServerTests\unit\models\_base\BaseOauth2ActiveRecordTest;

/**
 * @covers \rhertogh\Yii2Oauth2Server\models\Oauth2AccessTokenScope
 * @covers \rhertogh\Yii2Oauth2Server\models\base\Oauth2AccessTokenScope
 * @covers \rhertogh\Yii2Oauth2Server\models\base\Oauth2BaseActiveRecord
 * @covers \rhertogh\Yii2Oauth2Server\models\queries\Oauth2AccessTokenScopeQuery
 * @covers \rhertogh\Yii2Oauth2Server\models\queries\base\Oauth2BaseActiveQuery
 * @covers \rhertogh\Yii2Oauth2Server\models\behaviors\BooleanBehavior
 * @covers \rhertogh\Yii2Oauth2Server\models\behaviors\DateTimeBehavior
 * @covers \rhertogh\Yii2Oauth2Server\models\behaviors\TimestampBehavior
 *
 * @method Oauth2AccessTokenScopeInterface|ActiveRecord getMockModel(array $config = [])
 */
class Oauth2AccessTokenScopeTest extends BaseOauth2ActiveRecordTest
{
    /**
     * @return Oauth2AccessTokenScopeInterface|string
     */
    protected function getModelInterface()
    {
        return Oauth2AccessTokenScopeInterface::class;
    }

    /**
     * @return array[]
     * @see BaseOauth2ActiveRecordTest::testPersist()
     */
    public function persistTestProvider()
    {
        $this->mockConsoleApplication();
        return [
            // Valid
            [
                [
                    'access_token_id' => 1001000,
                    'scope_id' => 1005001,
                ],
                true,
            ],
            // Invalid (non-existing access token)
            [
                [
                    'access_token_id' => 999999,
                    'scope_id' => 1005001,
                ],
                false,
            ],
            // Invalid (non-existing scope)
            [
                [
                    'access_token_id' => 1001000,
                    'scope_id' => 999999,
                ],
                false,
            ],
        ];
    }
}
