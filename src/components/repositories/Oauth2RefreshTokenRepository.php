<?php

namespace rhertogh\Yii2Oauth2Server\components\repositories;

use League\OAuth2\Server\Entities\RefreshTokenEntityInterface;
use rhertogh\Yii2Oauth2Server\components\repositories\base\Oauth2BaseTokenRepository;
use rhertogh\Yii2Oauth2Server\components\repositories\traits\Oauth2ModelRepositoryTrait;
use rhertogh\Yii2Oauth2Server\interfaces\components\repositories\Oauth2RefreshTokenRepositoryInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\Oauth2RefreshTokenInterface;

class Oauth2RefreshTokenRepository extends Oauth2BaseTokenRepository implements Oauth2RefreshTokenRepositoryInterface
{
    use Oauth2ModelRepositoryTrait;

    /**
     * @inheritDoc
     * @return class-string<Oauth2RefreshTokenInterface>
     */
    public function getModelClass()
    {
        return Oauth2RefreshTokenInterface::class;
    }

    /**
     * @inheritDoc
     */
    public function getNewRefreshToken()
    {
        return static::getNewTokenInternally();
    }

    /**
     * @inheritDoc
     */
    public function persistNewRefreshToken(RefreshTokenEntityInterface $refreshTokenEntity)
    {
        static::persistToken($refreshTokenEntity);
    }

    /**
     * @inheritDoc
     */
    public function revokeRefreshToken($tokenId)
    {
        static::revokeToken($tokenId);
    }

    /**
     * @inheritDoc
     */
    public function isRefreshTokenRevoked($tokenId)
    {
        return static::isTokenRevoked($tokenId);
    }
}
