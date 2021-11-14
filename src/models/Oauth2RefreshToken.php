<?php

namespace rhertogh\Yii2Oauth2Server\models;

use DateTimeImmutable;
use League\OAuth2\Server\Entities\AccessTokenEntityInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\Oauth2AccessTokenInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\Oauth2RefreshTokenInterface;
use rhertogh\Yii2Oauth2Server\models\behaviors\DateTimeBehavior;
use rhertogh\Yii2Oauth2Server\models\traits\Oauth2ActiveRecordIdTrait;
use rhertogh\Yii2Oauth2Server\models\traits\Oauth2EntityIdentifierTrait;
use rhertogh\Yii2Oauth2Server\models\traits\Oauth2ExpiryDateTimeTrait;
use rhertogh\Yii2Oauth2Server\models\traits\Oauth2TokenTrait;
use yii\base\InvalidConfigException;
use yii\helpers\ArrayHelper;

/**
 * @property DateTimeImmutable $expiry_date_time
 * @property Oauth2AccessTokenInterface[] $accessTokenRelation
 */
class Oauth2RefreshToken extends base\Oauth2RefreshToken implements Oauth2RefreshTokenInterface
{
    use Oauth2ActiveRecordIdTrait;
    use Oauth2EntityIdentifierTrait;
    use Oauth2ExpiryDateTimeTrait;
    use Oauth2TokenTrait;

    /////////////////////////////
    /// ActiveRecord Settings ///
    /////////////////////////////

    /**
     * @inheritDoc
     */
    public function behaviors()
    {
        return ArrayHelper::merge(parent::behaviors(), [
            'dateTimeBehavior' => DateTimeBehavior::class
        ]);
    }

    /**
     * Wrapper for parent's getAccessToken() relation to avoid name conflicts
     */
    public function getAccessTokenRelation()
    {
        return parent::getAccessToken();
    }

    /////////////////////////
    /// Getters & Setters ///
    /////////////////////////

    /**
     * @inheritDoc
     */
    public function __set($name, $value)
    {
        if ($name === 'access_token_id' && $this->isRelationPopulated('accessTokenRelation')) {
            unset($this['accessTokenRelation']);
        }
        parent::__set($name, $value);
    }

    /**
     * @inheritDoc
     */
    public function setAttribute($name, $value)
    {
        if ($name === 'access_token_id' && $this->isRelationPopulated('accessTokenRelation')) {
            unset($this['accessTokenRelation']);
        }
        parent::setAttribute($name, $value);
    }

    /**
     * @inheritDoc
     * @throws InvalidConfigException
     */
    public function setAccessToken(AccessTokenEntityInterface $accessToken)
    {
        if (!($accessToken instanceof Oauth2AccessTokenInterface)) {
            throw new InvalidConfigException(
                get_class($accessToken) . ' must implement ' . Oauth2AccessTokenInterface::class
            );
        }

        $this->access_token_id = $accessToken->getPrimaryKey();
        $this->populateRelation('accessTokenRelation', $accessToken);
    }

    /**
     * @inheritDoc
     */
    public function getAccessToken()
    {
        return $this->accessTokenRelation;
    }
}
