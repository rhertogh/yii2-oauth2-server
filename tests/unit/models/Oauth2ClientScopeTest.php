<?php
namespace Yii2Oauth2ServerTests\unit\models;

use rhertogh\Yii2Oauth2Server\interfaces\models\Oauth2ClientScopeInterface;
use yii\db\ActiveRecord;
use Yii2Oauth2ServerTests\unit\models\_base\BaseOauth2ActiveRecordTest;

/**
 * @covers \rhertogh\Yii2Oauth2Server\models\Oauth2ClientScope
 * @covers \rhertogh\Yii2Oauth2Server\models\base\Oauth2ClientScope
 * @covers \rhertogh\Yii2Oauth2Server\models\base\Oauth2BaseActiveRecord
 * @covers \rhertogh\Yii2Oauth2Server\models\queries\Oauth2ClientScopeQuery
 * @covers \rhertogh\Yii2Oauth2Server\models\queries\base\Oauth2BaseActiveQuery
 * @covers \rhertogh\Yii2Oauth2Server\models\behaviors\BooleanBehavior
 * @covers \rhertogh\Yii2Oauth2Server\models\behaviors\DateTimeBehavior
 * @covers \rhertogh\Yii2Oauth2Server\models\behaviors\TimestampBehavior
 *
 * @method Oauth2ClientScopeInterface|ActiveRecord getMockModel(array $config = [])
 */
class Oauth2ClientScopeTest extends BaseOauth2ActiveRecordTest
{
    /**
     * @return Oauth2ClientScopeInterface|string
     */
    protected function getModelInterface()
    {
        return Oauth2ClientScopeInterface::class;
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
                    'client_id' => 1003000,
                    'scope_id' => 1005008,
                ],
                true,
            ],
            // Invalid (non-existing client)
            [
                [
                    'client_id' => 999999,
                    'scope_id' => 1005001,
                ],
                false,
            ],
            // Invalid (non-existing scope)
            [
                [
                    'client_id' => 1003000,
                    'scope_id' => 999999,
                ],
                false,
            ],
        ];
    }

    public function testPropertyGetters()
    {
        $clientScope = $this->getMockModel([
            'applied_by_default' => 1,
            'required_on_authorization' => 2,
        ]);

        $this->assertEquals(1, $clientScope->getAppliedByDefault());
        $this->assertEquals(2, $clientScope->getRequiredOnAuthorization());
    }
}
