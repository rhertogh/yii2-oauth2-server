<?php

namespace Yii2Oauth2ServerTests\unit\models;

use DateTimeImmutable;
use rhertogh\Yii2Oauth2Server\interfaces\models\Oauth2AuthCodeInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\Oauth2AuthCodeScopeInterface;
use yii\db\ActiveRecord;
use Yii2Oauth2ServerTests\unit\models\_base\BaseOauth2ActiveRecordTest;
use Yii2Oauth2ServerTests\unit\models\_traits\GetSetExpiryDateTimeTestTrait;
use Yii2Oauth2ServerTests\unit\models\_traits\GetSetRedirectUriTestTrait;
use Yii2Oauth2ServerTests\unit\models\_traits\GetSetRevokedStatusTestTrait;
use Yii2Oauth2ServerTests\unit\models\_traits\GetSetUserIdentifierTestTrait;
use Yii2Oauth2ServerTests\unit\models\_traits\Oauth2GetSetClientTestTrait;
use Yii2Oauth2ServerTests\unit\models\_traits\Oauth2IdentifierTestTrait;
use Yii2Oauth2ServerTests\unit\models\_traits\Oauth2IdTestTrait;
use Yii2Oauth2ServerTests\unit\models\_traits\Oauth2ScopesRelationTestTrait;

/**
 * @covers \rhertogh\Yii2Oauth2Server\models\Oauth2AuthCode
 * @covers \rhertogh\Yii2Oauth2Server\models\base\Oauth2AuthCode
 * @covers \rhertogh\Yii2Oauth2Server\models\base\Oauth2BaseActiveRecord
 * @covers \rhertogh\Yii2Oauth2Server\models\queries\Oauth2AuthCodeScopeQuery
 * @covers \rhertogh\Yii2Oauth2Server\models\queries\base\Oauth2BaseActiveQuery
 * @covers \rhertogh\Yii2Oauth2Server\models\behaviors\BooleanBehavior
 * @covers \rhertogh\Yii2Oauth2Server\models\behaviors\DateTimeBehavior
 * @covers \rhertogh\Yii2Oauth2Server\models\behaviors\TimestampBehavior
 *
 * @method Oauth2AuthCodeInterface|ActiveRecord getMockModel(array $config = [])
 */
class Oauth2AuthCodeTest extends BaseOauth2ActiveRecordTest
{
    use Oauth2IdTestTrait;
    use Oauth2IdentifierTestTrait;
    use Oauth2GetSetClientTestTrait;
    use Oauth2ScopesRelationTestTrait;
    use GetSetUserIdentifierTestTrait;
    use GetSetExpiryDateTimeTestTrait;
    use GetSetRedirectUriTestTrait;
    use GetSetRevokedStatusTestTrait;

    /**
     * @return class-string<Oauth2AuthCodeInterface>
     */
    protected function getModelInterface()
    {
        return Oauth2AuthCodeInterface::class;
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
                    'identifier' => 'my-test-auth-code',
                    'redirect_uri' => 'https://my.test/uri',
                    'expiry_date_time' => new DateTimeImmutable('now +1 hour'),
                    'client_id' => 1003000,
                    'user_id' => 123,
                ],
                true,
            ],
            // Invalid (non-existing client).
            [
                [
                    'identifier' => 'my-test-auth-code',
                    'redirect_uri' => 'https://my.test/uri',
                    'expiry_date_time' => new DateTimeImmutable('now +1 hour'),
                    'client_id' => 999999,
                    'user_id' => 123,
                ],
                false,
            ],
        ];
    }

    /**
     * @return int[][]
     * @see Oauth2IdTestTrait::testFindByPk()
     */
    public function findByPkTestProvider()
    {
        return [[1002000]];
    }

    /**
     * @return string[][]
     * @see Oauth2IdentifierTestTrait::testFindByIdentifier()
     */
    public function findByIdentifierTestProvider()
    {
        return [['test-auth-code-valid']];
    }

    /**
     * @return array[]
     * @see Oauth2IdentifierTestTrait::testIdentifierExists()
     */
    public function identifierExistsProvider()
    {
        return [
            ['test-auth-code-valid', true],
            ['does-not-exists',      false],
        ];
    }

    public function testGetScopesRelationClassName()
    {
        $authCode = $this->getMockModel();
        $this->assertTrue(is_a($authCode->getScopesRelationClassName(), Oauth2AuthCodeScopeInterface::class, true));
    }
}
