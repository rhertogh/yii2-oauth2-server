<?php

namespace Yii2Oauth2ServerTests\unit\components\repositories;

use DateTimeImmutable;
use League\OAuth2\Server\Exception\UniqueTokenIdentifierConstraintViolationException;
use rhertogh\Yii2Oauth2Server\interfaces\models\Oauth2AccessTokenInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\Oauth2AuthCodeInterface;
use rhertogh\Yii2Oauth2Server\interfaces\components\repositories\Oauth2AuthCodeRepositoryInterface;
use Yii;
use yii\base\InvalidConfigException;
use Yii2Oauth2ServerTests\unit\components\repositories\_base\BaseOauth2RepositoryTest;

/**
 * @covers \rhertogh\Yii2Oauth2Server\components\repositories\Oauth2AuthCodeRepository
 * @covers \rhertogh\Yii2Oauth2Server\components\repositories\base\Oauth2BaseTokenRepository
 *
 * @method Oauth2AuthCodeInterface|string getModelClass()
 */
class Oauth2AuthCodeRepositoryTest extends BaseOauth2RepositoryTest
{
    /**
     * @return Oauth2AuthCodeInterface|string
     */
    protected function getModelInterface()
    {
        return Oauth2AuthCodeInterface::class;
    }

    public function testGetNewAuthCode()
    {
        $authCode = $this->generateMockAuthCode();
        $this->assertInstanceOf($this->getModelInterface(), $authCode);
    }

    public function testPersistNewAuthCode()
    {
        $authCode = $this->generateMockAuthCode();

        $identifier = 'my-auth-code';
        $userIdentifier = 123;
        $expiryDateTime = new DateTimeImmutable('now +1 hour');
        $client = static::getClientClass()::findOne(['identifier' => 'test-client-type-auth-code-valid']);
        $scopes = static::getScopeClass()::findAll(['id' => [1005001, 1005002]]);
        if (empty($scopes)) {
            throw new InvalidConfigException('Failed to find scopes for generateMockAccessToken.');
        }

        $authCode->setIdentifier($identifier);
        $authCode->setExpiryDateTime($expiryDateTime);
        $authCode->setUserIdentifier($userIdentifier);
        $authCode->setScopes($scopes);
        $authCode->setClient($client);

        $this->getAuthCodeRepository()->persistNewAuthCode($authCode);

        $validateAuthCode = $this->getModelClass()::findOne(['identifier' => $identifier]);

        $this->assertInstanceOf($this->getModelInterface(), $validateAuthCode);
        $this->assertGreaterThanOrEqual(1, $validateAuthCode->getPrimaryKey());
        $this->assertEquals($identifier, $validateAuthCode->getIdentifier());
        $this->assertEquals($client->getIdentifier(), $validateAuthCode->getClient()->getIdentifier());
        $this->assertEquals($userIdentifier, $validateAuthCode->getUserIdentifier());
        $this->assertEquals($expiryDateTime->getTimestamp(), $validateAuthCode->getExpiryDateTime()->getTimestamp());
        $this->assertEquals(false, $validateAuthCode->getRevokedStatus());
        $this->assertEquals($scopes, $validateAuthCode->getScopes());
    }

    public function testPersistNewAuthCodeWithExistingIdentifier()
    {
        $authCode = $this->generateMockAuthCode();
        $authCode->setIdentifier('test-auth-code-valid');

        $this->expectException(UniqueTokenIdentifierConstraintViolationException::class);
        $this->getAuthCodeRepository()->persistNewAuthCode($authCode);
    }

    public function testRevokeAuthCode()
    {
        $identifier = 'test-auth-code-valid';
        $accessToken = $this->getModelClass()::findOne(['identifier' => $identifier]);

        $this->assertEquals(false, $accessToken->getRevokedStatus());

        $this->getAuthCodeRepository()->revokeAuthCode($identifier);
        $accessToken->refresh();

        $this->assertEquals(true, $accessToken->getRevokedStatus());
    }

    public function testIsAuthCodeRevoked()
    {
        $authCodeRepository = $this->getAuthCodeRepository();

        $this->assertEquals(false, $authCodeRepository->isAuthCodeRevoked('test-auth-code-valid'));
        $this->assertEquals(true, $authCodeRepository->isAuthCodeRevoked('test-auth-code-disabled'));
    }

    /**
     * @return Oauth2AuthCodeRepositoryInterface
     * @throws InvalidConfigException
     */
    protected function getAuthCodeRepository()
    {
        return Yii::createObject(Oauth2AuthCodeRepositoryInterface::class);
    }

    /**
     * @return Oauth2AccessTokenInterface
     * @throws \yii\base\InvalidConfigException
     */
    protected function generateMockAuthCode()
    {
        return $this->getAuthCodeRepository()->getNewAuthCode();
    }
}
