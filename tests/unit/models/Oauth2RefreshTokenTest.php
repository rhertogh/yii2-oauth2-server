<?php

namespace Yii2Oauth2ServerTests\unit\models;

use DateTimeImmutable;
use League\OAuth2\Server\Entities\AccessTokenEntityInterface;
use League\OAuth2\Server\Entities\Traits\AccessTokenTrait;
use League\OAuth2\Server\Entities\Traits\TokenEntityTrait;
use rhertogh\Yii2Oauth2Server\interfaces\models\Oauth2AccessTokenInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\Oauth2RefreshTokenInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\queries\Oauth2AccessTokenQueryInterface;
use Yii;
use yii\db\ActiveRecord;
use Yii2Oauth2ServerTests\unit\models\_base\BaseOauth2ActiveRecordTest;
use Yii2Oauth2ServerTests\unit\models\_traits\GetSetExpiryDateTimeTestTrait;
use Yii2Oauth2ServerTests\unit\models\_traits\GetSetRevokedStatusTestTrait;
use Yii2Oauth2ServerTests\unit\models\_traits\Oauth2IdentifierTestTrait;
use Yii2Oauth2ServerTests\unit\models\_traits\Oauth2IdTestTrait;

/**
 * @covers \rhertogh\Yii2Oauth2Server\models\Oauth2RefreshToken
 * @covers \rhertogh\Yii2Oauth2Server\models\base\Oauth2RefreshToken
 * @covers \rhertogh\Yii2Oauth2Server\models\base\Oauth2BaseActiveRecord
 * @covers \rhertogh\Yii2Oauth2Server\models\queries\Oauth2RefreshTokenQuery
 * @covers \rhertogh\Yii2Oauth2Server\models\queries\base\Oauth2BaseActiveQuery
 * @covers \rhertogh\Yii2Oauth2Server\models\behaviors\BooleanBehavior
 * @covers \rhertogh\Yii2Oauth2Server\models\behaviors\DateTimeBehavior
 * @covers \rhertogh\Yii2Oauth2Server\models\behaviors\TimestampBehavior
 *
 * @method Oauth2RefreshTokenInterface|ActiveRecord getMockModel(array $config = [])
 */

class Oauth2RefreshTokenTest extends BaseOauth2ActiveRecordTest
{
    use Oauth2IdTestTrait;
    use Oauth2IdentifierTestTrait;
    use GetSetExpiryDateTimeTestTrait;
    use GetSetRevokedStatusTestTrait;

    /**
     * @return class-string<Oauth2RefreshTokenInterface>
     */
    protected function getModelInterface()
    {
        return Oauth2RefreshTokenInterface::class;
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
                    'access_token_id' => 1001000,
                    'identifier' => 'my-test-refresh-token',
                    'expiry_date_time' => new DateTimeImmutable('now +1 hour'),
                ],
                true,
            ],
            // Invalid (non-existing access token).
            [
                [
                    'access_token_id' => 999999,
                    'identifier' => 'my-test-refresh-token',
                    'expiry_date_time' => new DateTimeImmutable('now +1 hour'),
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
        return [[1004000]];
    }

    /**
     * @return string[][]
     * @see Oauth2IdentifierTestTrait::testFindByIdentifier()
     */
    public function findByIdentifierTestProvider()
    {
        return [['test-refresh-token-valid']];
    }

    /**
     * @return array[]
     * @see Oauth2IdentifierTestTrait::testIdentifierExists()
     */
    public function identifierExistsProvider()
    {
        return [
            ['test-refresh-token-valid', true],
            ['does-not-exists',          false],
        ];
    }

    public function testGetSetAccessToken()
    {
        $mocks = $this->getMockModelAndAccessToken(); // Workaround for https://youtrack.jetbrains.com/issue/WI-61907.
        $model = $mocks['model'];
        $accessTokenId = $mocks['accessTokenId'];
        $accessToken = $mocks['accessToken'];

        $this->assertNull($model->getAccessToken());
        $model->setAccessToken($accessToken);
        $this->assertEquals($accessToken, $model->getAccessToken());
        $this->assertEquals($accessTokenId, $model->getAttribute('access_token_id'));
        $this->assertEquals($accessTokenId, $model->access_token_id);
    }

    /**
     * @depends testGetSetAccessToken
     */
    public function testSetAttributeAccessTokenId()
    {
        $mocks = $this->getMockModelAndAccessToken(); // Workaround for https://youtrack.jetbrains.com/issue/WI-61907.
        $model = $mocks['model'];
        $accessToken = $mocks['accessToken'];

        $model->setAccessToken($accessToken);
        $model->setAttribute('access_token_id', 456);
        $this->assertNull($model->getAccessToken());
    }

    /**
     * @depends testGetSetAccessToken
     */
    public function testSetAccessTokenId()
    {
        $mocks = $this->getMockModelAndAccessToken(); // Workaround for https://youtrack.jetbrains.com/issue/WI-61907.
        $model = $mocks['model'];
        $accessToken = $mocks['accessToken'];

        $model->setAccessToken($accessToken);
        $model->access_token_id = 456;
        $this->assertNull($model->getAccessToken());
    }

    public function testSetInvalidAccessToken()
    {
        $model = $this->getMockModel();

        $mockAccessToken = new class implements AccessTokenEntityInterface {
            use AccessTokenTrait;
            use TokenEntityTrait;

            public function getIdentifier()
            {
                return null;
            }

            public function setIdentifier($identifier)
            {
            }
        };

        $this->expectExceptionMessage(
            get_class($mockAccessToken) . ' must implement ' . Oauth2AccessTokenInterface::class
        );
        $model->setAccessToken($mockAccessToken);
    }

    public function testGetAccessTokenRelation()
    {
        $model = $this->getMockModel();
        $this->assertInstanceOf(Oauth2AccessTokenQueryInterface::class, $model->getAccessTokenRelation());
    }


    /**
     * // phpcs:ignore Generic.Files.LineLength.TooLong -- single line is required for PhpStorm
     * @return array{model: Oauth2RefreshTokenInterface|ActiveRecord, accessTokenId: int, accessToken: Oauth2AccessTokenInterface}
     * @throws \yii\base\InvalidConfigException
     */
    protected function getMockModelAndAccessToken()
    {
        $model = $this->getMockModel();
        $accessTokenId = 123;

        /** @var Oauth2AccessTokenInterface $accessToken */
        $accessToken = Yii::createObject([
            'class' => Oauth2AccessTokenInterface::class,
            'id' => $accessTokenId,
        ]);

        return [
            'model' => $model,
            'accessTokenId' => $accessTokenId,
            'accessToken' => $accessToken,
        ];
    }
}
