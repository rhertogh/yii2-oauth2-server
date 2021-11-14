<?php

namespace Yii2Oauth2ServerTests\unit\components\repositories;

use DateTimeImmutable;
use League\OAuth2\Server\Exception\UniqueTokenIdentifierConstraintViolationException;
use rhertogh\Yii2Oauth2Server\interfaces\components\repositories\Oauth2AccessTokenRepositoryInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\Oauth2AccessTokenInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\Oauth2ClientInterface;
use rhertogh\Yii2Oauth2Server\models\Oauth2AccessToken;
use Yii;
use yii\base\InvalidConfigException;
use Yii2Oauth2ServerTests\unit\components\repositories\_base\BaseOauth2RepositoryTest;

/**
 * @covers \rhertogh\Yii2Oauth2Server\components\repositories\Oauth2AccessTokenRepository
 * @covers \rhertogh\Yii2Oauth2Server\components\repositories\base\Oauth2BaseTokenRepository
 *
 * @method Oauth2AccessTokenInterface|string getModelClass()
 */
class Oauth2AccessTokenRepositoryTest extends BaseOauth2RepositoryTest
{
    /**
     * @return Oauth2AccessTokenInterface|string
     */
    protected function getModelInterface()
    {
        return Oauth2AccessTokenInterface::class;
    }

    public function testGetNewToken()
    {
        list (
            'accessToken' => $accessToken,
            'client' => $client,
            'scopes' => $scopes,
            'userIdentifier' => $userIdentifier
        ) = $this->generateMockAccessToken();

        $this->assertInstanceOf($this->getModelInterface(), $accessToken);

        $this->assertEquals($userIdentifier, $accessToken->getUserIdentifier());

        $this->assertEquals($client->getIdentifier(), $accessToken->getClient()->getIdentifier());

        $this->assertEquals($scopes, $accessToken->getScopes());
    }

    public function testPersistNewAccessToken()
    {
        /** @var Oauth2AccessTokenInterface $accessToken */
        /** @var Oauth2ClientInterface $client */
        [
            'accessToken' => $accessToken,
            'client' => $client,
            'scopes' => $scopes,
            'userIdentifier' => $userIdentifier
        ] = $this->generateMockAccessToken();

        $identifier = 'my-access-token';
        $expiryDateTime = new DateTimeImmutable('now +1 hour');
        $accessToken->setIdentifier($identifier);
        $accessToken->setExpiryDateTime($expiryDateTime);

        $this->getAccessTokenRepository()->persistNewAccessToken($accessToken);

        $validateAccessToken = $this->getModelClass()::findOne(['identifier' => $identifier]);

        $this->assertInstanceOf($this->getModelInterface(), $validateAccessToken);
        $this->assertGreaterThanOrEqual(1, $validateAccessToken->getPrimaryKey());
        $this->assertEquals($identifier, $validateAccessToken->getIdentifier());
        $this->assertEquals($client->getIdentifier(), $validateAccessToken->getClient()->getIdentifier());
        $this->assertEquals($userIdentifier, $validateAccessToken->getUserIdentifier());
        $this->assertEquals(Oauth2AccessToken::TYPE_BEARER, $validateAccessToken->type); // Fixed type for now.
        $this->assertEquals($expiryDateTime->getTimestamp(), $validateAccessToken->getExpiryDateTime()->getTimestamp());
        $this->assertEquals(false, $validateAccessToken->getRevokedStatus());
        $this->assertEquals($scopes, $validateAccessToken->getScopes());
    }

    public function testPersistNewAccessTokenWithExistingIdentifier()
    {
        /** @var Oauth2AccessTokenInterface $accessToken */
        $accessToken = $this->generateMockAccessToken()['accessToken'];
        $accessToken->setIdentifier('test-access-token-bearer-active');

        $this->expectException(UniqueTokenIdentifierConstraintViolationException::class);
        $this->getAccessTokenRepository()->persistNewAccessToken($accessToken);
    }

    public function testRevokeAccessToken()
    {
        $identifier = 'test-access-token-bearer-active';
        $accessToken = $this->getModelClass()::findOne(['identifier' => $identifier]);

        $this->assertEquals(false, $accessToken->getRevokedStatus());

        $this->getAccessTokenRepository()->revokeAccessToken($identifier);
        $accessToken->refresh();

        $this->assertEquals(true, $accessToken->getRevokedStatus());
    }

    public function testIsAccessTokenRevoked()
    {
        $accessTokenRepository = $this->getAccessTokenRepository();

        $accessTokenRepository->setRevocationValidation(false);
        $this->assertEquals(false, $accessTokenRepository->isAccessTokenRevoked('non-existing'));
        $accessTokenRepository->setRevocationValidation(true);
        $this->assertEquals(true, $accessTokenRepository->isAccessTokenRevoked('non-existing'));
        $this->assertEquals(false, $accessTokenRepository->isAccessTokenRevoked('test-access-token-bearer-active'));
        $this->assertEquals(true, $accessTokenRepository->isAccessTokenRevoked('test-access-token-bearer-disabled'));

        $validation = fn($identifier) => $identifier == 'yes';
        $accessTokenRepository->setRevocationValidation($validation);
        $this->assertEquals(true, $accessTokenRepository->isAccessTokenRevoked('yes'));
        $this->assertEquals(false, $accessTokenRepository->isAccessTokenRevoked('no'));
    }

    public function testIsAccessTokenRevokedInvalidRevocationValidation()
    {
        $accessTokenRepository = $this->getAccessTokenRepository();
        $this->expectExceptionMessage('Access Token Revocation Validation must be a boolean or callable');
        $this->setInaccessibleProperty($accessTokenRepository, '_revocationValidation', new \stdClass());
        $accessTokenRepository->isAccessTokenRevoked('test-access-token-bearer-active');
    }

    /**
     * @return Oauth2AccessTokenRepositoryInterface
     * @throws InvalidConfigException
     */
    protected function getAccessTokenRepository()
    {
        return Yii::createObject(Oauth2AccessTokenRepositoryInterface::class);
    }

    /**
     * @return array
     * @throws InvalidConfigException
     */
    protected function generateMockAccessToken()
    {
        $client = static::getClientClass()::findOne(['identifier' => 'test-client-type-auth-code-valid']);
        $scopes = static::getScopeClass()::findAll(['id' => [1005001, 1005002]]);
        if (empty($scopes)) {
            throw new InvalidConfigException('Failed to find scopes for generateMockAccessToken.');
        }
        $userIdentifier = 123;

        /** @var Oauth2AccessTokenInterface $accessToken */
        $accessToken = $this->getAccessTokenRepository()->getNewToken($client, $scopes, $userIdentifier);

        return [
            'accessToken' => $accessToken,
            'client' => $client,
            'scopes' => $scopes,
            'userIdentifier' => $userIdentifier,
        ];
    }
}
