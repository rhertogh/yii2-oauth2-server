<?php

namespace rhertogh\Yii2Oauth2Server\components\repositories;

use League\OAuth2\Server\Entities\AccessTokenEntityInterface;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use rhertogh\Yii2Oauth2Server\components\repositories\base\Oauth2BaseTokenRepository;
use rhertogh\Yii2Oauth2Server\components\repositories\traits\Oauth2ModelRepositoryTrait;
use rhertogh\Yii2Oauth2Server\helpers\DiHelper;
use rhertogh\Yii2Oauth2Server\interfaces\components\repositories\Oauth2AccessTokenRepositoryInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\base\Oauth2IdentifierInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\base\Oauth2UserIdentifierInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\Oauth2AccessTokenInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\Oauth2ClientInterface;
use yii\base\InvalidConfigException;
use yii\db\Connection;

class Oauth2AccessTokenRepository extends Oauth2BaseTokenRepository implements Oauth2AccessTokenRepositoryInterface
{
    use Oauth2ModelRepositoryTrait;

    /**
     * @var bool
     */
    protected $_revocationValidation = true;

    /**
     * @inheritDoc
     * @return class-string<Oauth2AccessTokenInterface>
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

    /**
     * @inheritDoc
     */
    public function revokeAccessTokensByUserId($userId)
    {
        $class = $this->getModelClass();
        /** @var class-string<Oauth2AccessTokenInterface> $className */
        $className = DiHelper::getValidatedClassName($class);

        $db = $className::getDb();

        $transaction = $db->beginTransaction();

        try {
            /** @var Oauth2AccessTokenInterface[] $accessTokens */
            $accessTokens = $className::findAllByUserId($userId);
            foreach ($accessTokens as $accessToken) {
                $accessToken->setRevokedStatus(true);
                $accessToken->persist();
            }
            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }

        return $accessTokens;
    }
}
