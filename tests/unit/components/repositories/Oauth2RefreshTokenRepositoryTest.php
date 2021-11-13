<?php

namespace Yii2Oauth2ServerTests\unit\components\repositories;

use DateTimeImmutable;
use League\OAuth2\Server\Exception\UniqueTokenIdentifierConstraintViolationException;
use League\OAuth2\Server\Repositories\RefreshTokenRepositoryInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\Oauth2AccessTokenInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\Oauth2AuthCodeInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\Oauth2RefreshTokenInterface;
use rhertogh\Yii2Oauth2Server\interfaces\components\repositories\Oauth2AuthCodeRepositoryInterface;
use rhertogh\Yii2Oauth2Server\interfaces\components\repositories\Oauth2RefreshTokenRepositoryInterface;
use Yii;
use yii\base\InvalidConfigException;
use Yii2Oauth2ServerTests\unit\components\repositories\_base\BaseOauth2RepositoryTest;

/**
 * @covers \rhertogh\Yii2Oauth2Server\components\repositories\Oauth2RefreshTokenRepository
 * @covers \rhertogh\Yii2Oauth2Server\components\repositories\base\Oauth2BaseTokenRepository
 *
 * @method Oauth2RefreshTokenInterface|string getModelClass()
 */
class Oauth2RefreshTokenRepositoryTest extends BaseOauth2RepositoryTest
{
    /**
     * @return Oauth2RefreshTokenInterface|string
     */
    protected function getModelInterface()
    {
        return Oauth2RefreshTokenInterface::class;
    }

    public function testGetRefreshToken()
    {
        $refreshToken = $this->generateMockRefreshToken();
        $this->assertInstanceOf($this->getModelInterface(), $refreshToken);
    }

    public function testPersistNewRefreshToken()
    {
        $refreshToken = $this->generateMockRefreshToken();

        $identifier = 'my-refresh-token';
        $expiryDateTime = new DateTimeImmutable('now +1 hour');
        $accessToken = static::getAccessTokenClass()::findOne(['identifier' => 'test-access-token-bearer-active']);

        $refreshToken->setIdentifier($identifier);
        $refreshToken->setExpiryDateTime($expiryDateTime);
        $refreshToken->setAccessToken($accessToken);

        $this->getRefreshTokenRepository()->persistNewRefreshToken($refreshToken);

        $validateRefreshToken = $this->getModelClass()::findOne(['identifier' => $identifier]);

        // phpcs:disable Generic.Files.LineLength.TooLong -- readability acually better on single line
        $this->assertInstanceOf($this->getModelInterface(), $validateRefreshToken);
        $this->assertGreaterThanOrEqual(1, $validateRefreshToken->getPrimaryKey());
        $this->assertEquals($identifier, $validateRefreshToken->getIdentifier());
        $this->assertEquals($accessToken->getIdentifier(), $validateRefreshToken->getAccessToken()->getIdentifier());
        $this->assertEquals($expiryDateTime->getTimestamp(), $validateRefreshToken->getExpiryDateTime()->getTimestamp());
        $this->assertEquals(false, $validateRefreshToken->getRevokedStatus());
        // phpcs:enable Generic.Files.LineLength.TooLong
    }

    public function testPersistNewRefreshTokenWithExistingIdentifier()
    {
        $refreshToken = $this->generateMockRefreshToken();
        $refreshToken->setIdentifier('test-refresh-token-valid');

        $this->expectException(UniqueTokenIdentifierConstraintViolationException::class);
        $this->getRefreshTokenRepository()->persistNewRefreshToken($refreshToken);
    }

    public function testRevokeRefreshToken()
    {
        $identifier = 'test-refresh-token-valid';
        $refreshToken = $this->getModelClass()::findOne(['identifier' => $identifier]);

        $this->assertEquals(false, $refreshToken->getRevokedStatus());

        $this->getRefreshTokenRepository()->revokeRefreshToken($identifier);
        $refreshToken->refresh();

        $this->assertEquals(true, $refreshToken->getRevokedStatus());
    }

    public function testIsRefreshTokenRevoked()
    {
        $refreshTokenRepository = $this->getRefreshTokenRepository();

        $this->assertEquals(false, $refreshTokenRepository->isRefreshTokenRevoked('test-refresh-token-valid'));
        $this->assertEquals(true, $refreshTokenRepository->isRefreshTokenRevoked('test-refresh-token-disabled'));
    }

    /**
     * @return Oauth2RefreshTokenRepositoryInterface
     * @throws InvalidConfigException
     */
    protected function getRefreshTokenRepository()
    {
        return Yii::createObject(Oauth2RefreshTokenRepositoryInterface::class);
    }

    /**
     * @return Oauth2RefreshTokenInterface
     * @throws \yii\base\InvalidConfigException
     */
    protected function generateMockRefreshToken()
    {
        return $this->getRefreshTokenRepository()->getNewRefreshToken();
    }
}
