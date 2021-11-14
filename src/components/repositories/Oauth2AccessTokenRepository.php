<?php

namespace rhertogh\Yii2Oauth2Server\components\repositories;

use League\OAuth2\Server\Entities\AccessTokenEntityInterface;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use rhertogh\Yii2Oauth2Server\components\repositories\base\Oauth2BaseTokenRepository;
use rhertogh\Yii2Oauth2Server\components\repositories\traits\Oauth2RepositoryIdentifierTrait;
use rhertogh\Yii2Oauth2Server\interfaces\components\repositories\Oauth2AccessTokenRepositoryInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\Oauth2AccessTokenInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\Oauth2ClientInterface;
use yii\base\InvalidConfigException;

class Oauth2AccessTokenRepository extends Oauth2BaseTokenRepository implements Oauth2AccessTokenRepositoryInterface
{
    use Oauth2RepositoryIdentifierTrait;

    /**
     * @var bool
     */
    protected $_revocationValidation = true;

    /**
     * @inheritDoc
     * @return Oauth2AccessTokenInterface|string
     */
    public function getModelClass()
    {
        return Oauth2AccessTokenInterface::class;
    }

    /**
     * @inheritDoc
     * @param Oauth2ClientInterface $clientEntity
     * @throws InvalidConfigException
     */
    public function getNewToken(ClientEntityInterface $clientEntity, array $scopes, $userIdentifier = null)
    {
        /** @var Oauth2AccessTokenInterface $accessToken */
        $accessToken = static::getNewTokenInternally([
            'client' => $clientEntity,
            'userIdentifier' => $userIdentifier,
        ]);

        $accessToken->setScopes($scopes);

        return $accessToken;
    }

    /**
     * @inheritDoc
     * @throws \yii\db\Exception
     * @throws InvalidConfigException
     */
    public function persistNewAccessToken(AccessTokenEntityInterface $accessTokenEntity)
    {
        static::persistToken($accessTokenEntity);
    }

    /**
     * @inheritDoc
     */
    public function revokeAccessToken($tokenIdentifier)
    {
        static::revokeToken($tokenIdentifier);
    }

    /**
     * @inheritDoc
     */
    public function isAccessTokenRevoked($tokenIdentifier)
    {
        $validation = $this->getRevocationValidation();
        if ($validation === true) {
            return static::isTokenRevoked($tokenIdentifier);
        } elseif ($validation === false) {
            return false;
        } elseif (is_callable($validation)) {
            return call_user_func($validation, $tokenIdentifier);
        } else {
            throw new InvalidConfigException('Access Token Revocation Validation must be a boolean or callable');
        }
    }

    /**
     * @inheritDoc
     */
    public function getRevocationValidation()
    {
        return $this->_revocationValidation;
    }

    /**
     * @inheritDoc
     */
    public function setRevocationValidation($validation)
    {
        $this->_revocationValidation = $validation;
        return $this;
    }
}
