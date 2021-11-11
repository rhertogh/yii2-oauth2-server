<?php
namespace Yii2Oauth2ServerTests\unit\models;

use rhertogh\Yii2Oauth2Server\interfaces\models\Oauth2ScopeInterface;
use rhertogh\Yii2Oauth2Server\models\Oauth2ClientScope;
use rhertogh\Yii2Oauth2Server\models\Oauth2Scope;
use yii\db\ActiveRecord;
use Yii2Oauth2ServerTests\unit\models\_base\BaseOauth2ActiveRecordTest;
use Yii2Oauth2ServerTests\unit\models\_traits\Oauth2IdentifierTestTrait;
use Yii2Oauth2ServerTests\unit\models\_traits\Oauth2IdTestTrait;

/**
 * @covers \rhertogh\Yii2Oauth2Server\models\Oauth2Scope
 * @covers \rhertogh\Yii2Oauth2Server\models\base\Oauth2Scope
 * @covers \rhertogh\Yii2Oauth2Server\models\base\Oauth2BaseActiveRecord
 * @covers \rhertogh\Yii2Oauth2Server\models\queries\Oauth2ScopeQuery
 * @covers \rhertogh\Yii2Oauth2Server\models\queries\base\Oauth2BaseActiveQuery
 * @covers \rhertogh\Yii2Oauth2Server\models\behaviors\BooleanBehavior
 * @covers \rhertogh\Yii2Oauth2Server\models\behaviors\DateTimeBehavior
 * @covers \rhertogh\Yii2Oauth2Server\models\behaviors\TimestampBehavior
 *
 * @method Oauth2ScopeInterface|ActiveRecord getMockModel(array $config = [])
 */
class Oauth2ScopeTest extends BaseOauth2ActiveRecordTest
{
    use Oauth2IdTestTrait;
    use Oauth2IdentifierTestTrait;

    /**
     * @return Oauth2ScopeInterface|string
     */
    protected function getModelInterface()
    {
        return Oauth2ScopeInterface::class;
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
                    'identifier' => 'my-test-scope',
                ],
                true,
            ],
        ];
    }

    /**
     * @return int[][]
     * @see Oauth2IdTestTrait::testFindById()
     */
    public function findByIdTestProvider()
    {
        return [[1005001]];
    }

    /**
     * @return string[][]
     * @see Oauth2IdentifierTestTrait::testFindByIdentifier()
     */
    public function findByIdentifierTestProvider()
    {
        return [['user.username.read']];
    }

    /**
     * @return array[]
     * @see Oauth2IdentifierTestTrait::testIdentifierExists()
     */
    public function identifierExistsProvider()
    {
        return [
            ['user.username.read',    true],
            ['does-not-exists', false],
        ];
    }

    public function testJsonSerialize()
    {
        $identifier = 'my-test-scope';

        $model = $this->getMockModel([
            'identifier' => $identifier,
        ]);

        $this->assertEquals($identifier, $model->jsonSerialize());
    }

    public function testPropertyGetters()
    {
        $scope = new Oauth2Scope([
            'description' => 'test-description',
            'applied_by_default' => 1,
            'required_on_authorization' => 1,
        ]);

        $this->assertEquals('test-description', $scope->getDescription());
        $this->assertEquals(1, $scope->getAppliedByDefault());
        $this->assertEquals(1, $scope->getRequiredOnAuthorization());
    }

    public function testGetClientScope()
    {
        $scope = Oauth2Scope::findOne(['identifier' => 'user.id.read']);
        $this->assertEquals('test-client-type-auth-code-valid', $scope->getClientScope(1003000)->client->identifier);

        //Pre-populated relation test
        $clientScope = new Oauth2ClientScope([
            'client_id' => 1,
        ]);
        $scope = new Oauth2Scope();
        $scope->populateRelation('clientScopes', [$clientScope]);

        $this->assertEquals($clientScope, $scope->getClientScope(1));
        $this->assertNull($scope->getClientScope(2));
    }
}
