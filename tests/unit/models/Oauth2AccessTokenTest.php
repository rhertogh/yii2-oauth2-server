<?php

namespace Yii2Oauth2ServerTests\unit\models;

use DateTimeImmutable;
use League\OAuth2\Server\CryptKey;
use rhertogh\Yii2Oauth2Server\interfaces\models\Oauth2AccessTokenInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\Oauth2AccessTokenScopeInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\Oauth2AuthCodeScopeInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\Oauth2ClientInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\Oauth2ScopeInterface;
use rhertogh\Yii2Oauth2Server\models\Oauth2AccessToken;
use Yii;
use yii\db\ActiveRecord;
use Yii2Oauth2ServerTests\_helpers\ClassHelper;
use Yii2Oauth2ServerTests\unit\models\_base\BaseOauth2ActiveRecordTest;
use Yii2Oauth2ServerTests\unit\models\_traits\GetSetExpiryDateTimeTestTrait;
use Yii2Oauth2ServerTests\unit\models\_traits\GetSetRevokedStatusTestTrait;
use Yii2Oauth2ServerTests\unit\models\_traits\GetSetUserIdentifierTestTrait;
use Yii2Oauth2ServerTests\unit\models\_traits\Oauth2GetSetClientTestTrait;
use Yii2Oauth2ServerTests\unit\models\_traits\Oauth2IdentifierTestTrait;
use Yii2Oauth2ServerTests\unit\models\_traits\Oauth2IdTestTrait;
use Yii2Oauth2ServerTests\unit\models\_traits\Oauth2ScopesRelationTestTrait;

/**
 * @covers \rhertogh\Yii2Oauth2Server\models\Oauth2AccessToken
 * @covers \rhertogh\Yii2Oauth2Server\models\base\Oauth2AccessToken
 * @covers \rhertogh\Yii2Oauth2Server\models\base\Oauth2BaseActiveRecord
 * @covers \rhertogh\Yii2Oauth2Server\models\queries\Oauth2AccessTokenQuery
 * @covers \rhertogh\Yii2Oauth2Server\models\queries\base\Oauth2BaseActiveQuery
 * @covers \rhertogh\Yii2Oauth2Server\models\behaviors\BooleanBehavior
 * @covers \rhertogh\Yii2Oauth2Server\models\behaviors\DateTimeBehavior
 * @covers \rhertogh\Yii2Oauth2Server\models\behaviors\TimestampBehavior
 *
 * @method Oauth2AccessTokenInterface|ActiveRecord getMockModel(array $config = [])
 */

class Oauth2AccessTokenTest extends BaseOauth2ActiveRecordTest
{
    use Oauth2IdTestTrait;
    use Oauth2IdentifierTestTrait;
    use Oauth2GetSetClientTestTrait;
    use Oauth2ScopesRelationTestTrait;
    use GetSetUserIdentifierTestTrait;
    use GetSetExpiryDateTimeTestTrait;
    use GetSetRevokedStatusTestTrait;

    /**
     * @return Oauth2AccessTokenInterface|string
     */
    protected function getModelInterface()
    {
        return Oauth2AccessTokenInterface::class;
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
                    'identifier' => 'my-test-access-token',
                    'client_id' => 1003000,
                    'user_id' => 123,
                    'type' => Oauth2AccessToken::TYPE_BEARER,
                    'expiry_date_time' => new DateTimeImmutable('now +1 hour'),
                ],
                true,
            ],
            // Valid (but disabled)
            [
                [
                    'identifier' => 'my-test-access-token',
                    'client_id' => 1003000,
                    'user_id' => 123,
                    'type' => Oauth2AccessToken::TYPE_BEARER,
                    'expiry_date_time' => new DateTimeImmutable('now +1 hour'),
                    'enabled' => false,
                ],
                true,
            ],
            // Invalid (missing identifier)
            [
                [
                    'client_id' => 1003000,
                    'user_id' => 123,
                    'type' => Oauth2AccessToken::TYPE_BEARER,
                    'expiry_date_time' => new DateTimeImmutable('now +1 hour'),
                ],
                false,
            ],
        ];
    }

    /**
     * @return int[][]
     * @see Oauth2IdTestTrait::testFindById()
     */
    public function findByIdTestProvider()
    {
        return [[1001000]];
    }

    /**
     * @return string[][]
     * @see Oauth2IdentifierTestTrait::testFindByIdentifier()
     */
    public function findByIdentifierTestProvider()
    {
        return [['test-access-token-bearer-active']];
    }

    /**
     * @return array[]
     * @see Oauth2IdentifierTestTrait::testIdentifierExists()
     */
    public function identifierExistsProvider()
    {
        return [
            ['test-access-token-bearer-active', true],
            ['does-not-exists',                 false],
        ];
    }

    public function testSetPrivateKey()
    {
        $accessToken = $this->getMockModel();
        $key = $this->getMockPrivateCryptKey();
        $this->assertEmpty($this->getInaccessibleProperty($accessToken, 'privateKey'));
        $accessToken->setPrivateKey($key);
        /** @var CryptKey $actualKey */
        $actualKey = $this->getInaccessibleProperty($accessToken, 'privateKey');
        $this->assertEquals($key->getKeyContents(), $actualKey->getKeyContents());
    }

    public function testToString()
    {
        /** @var Oauth2ClientInterface $client */
        $client = Yii::createObject([
            'class' => Oauth2ClientInterface::class,
            'id' => 101,
            'identifier' => 'my-test-client'
        ]);

        /** @var Oauth2ScopeInterface[] $scopes */
        $scopes = [
            Yii::createObject([
                'class' => Oauth2ScopeInterface::class,
                'id' => 1,
                'identifier' => 'my-test-scope-1',
            ]),
            Yii::createObject([
                'class' => Oauth2ScopeInterface::class,
                'id' => 2,
                'identifier' => 'my-test-scope-2',
            ]),
        ];

        $identifier = 'my-test-token';
        $expiryDateTime = new DateTimeImmutable('now +1 hour');
        $userIdentifier = 123;

        $accessToken = $this->getMockModel([
            'privateKey' => $this->getMockPrivateCryptKey(),
            'client' => $client,
            'identifier' => $identifier,
            'expiryDateTime' => $expiryDateTime,
            'userIdentifier' => $userIdentifier,
            'scopes' => $scopes,
        ]);

        $jwtString = $accessToken->__toString();

        $jwtConfiguration = $this->getBearerTokenValidatorHelper()->getJwtConfiguration();

        $jwt = $jwtConfiguration->parser()->parse($jwtString);

        $constraints = $jwtConfiguration->validationConstraints();
        $jwtConfiguration->validator()->assert($jwt, ...$constraints);

        $claims = $jwt->claims();
        $this->assertEquals($identifier, $claims->get('jti'));
        $this->assertEquals($expiryDateTime, $claims->get('exp'));
        $this->assertEquals($userIdentifier, $claims->get('sub'));
        $this->assertEquals(array_column($scopes, 'identifier'), $claims->get('scopes'));
    }


    public function testGetScopesRelationClassName()
    {
        $accessToken = $this->getMockModel();
        $this->assertTrue(is_a($accessToken->getScopesRelationClassName(), Oauth2AccessTokenScopeInterface::class, true));
    }
}
