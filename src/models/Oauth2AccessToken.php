<?php

namespace rhertogh\Yii2Oauth2Server\models;

use DateTimeImmutable;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Entities\Traits\AccessTokenTrait;
use rhertogh\Yii2Oauth2Server\interfaces\models\base\Oauth2ActiveRecordInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\Oauth2AccessTokenInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\Oauth2AccessTokenScopeInterface;
use rhertogh\Yii2Oauth2Server\models\behaviors\DateTimeBehavior;
use rhertogh\Yii2Oauth2Server\models\traits\Oauth2ActiveRecordIdTrait;
use rhertogh\Yii2Oauth2Server\models\traits\Oauth2ClientRelationTrait;
use rhertogh\Yii2Oauth2Server\models\traits\Oauth2EntityIdentifierTrait;
use rhertogh\Yii2Oauth2Server\models\traits\Oauth2ExpiryDateTimeTrait;
use rhertogh\Yii2Oauth2Server\models\traits\Oauth2ScopesRelationTrait;
use rhertogh\Yii2Oauth2Server\models\traits\Oauth2TokenTrait;
use rhertogh\Yii2Oauth2Server\models\traits\Oauth2UserIdentifierTrait;
use yii\helpers\ArrayHelper;

/**
 * @property DateTimeImmutable $expiry_date_time
 * @property Oauth2Client $clientRelation
 * @property Oauth2Scope[] $scopesRelation
 */
class Oauth2AccessToken extends base\Oauth2AccessToken implements Oauth2AccessTokenInterface
{
    use Oauth2ActiveRecordIdTrait;
    use Oauth2EntityIdentifierTrait;
    use AccessTokenTrait;
    use Oauth2TokenTrait;
    use Oauth2ExpiryDateTimeTrait;
    use Oauth2UserIdentifierTrait;
    use Oauth2ScopesRelationTrait;
    use Oauth2ClientRelationTrait {
        __set as clientRelationSetter;
        setClient as clientSetter;
    }

    public const TYPE_BEARER = 1;
    public const TYPE_MAC = 2;

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

    /////////////////////////
    /// Getters & Setters ///
    /////////////////////////

    /**
     * @inheritDoc
     */
    public function setClient(ClientEntityInterface $client)
    {
        $this->clientSetter($client);
        $this->type = Oauth2AccessToken::TYPE_BEARER; // Fixed for now.
    }

    /**
     * @inheritDoc
     */
    public function __set($name, $value)
    {
        // wrapper function to ensure the __set function of the Oauth2ClientRelationTrait is never overwritten.
        $this->clientRelationSetter($name, $value);
    }

    /**
     * @inheritDoc
     */
    public function getScopesRelationClassName()
    {
        return Oauth2AccessTokenScopeInterface::class;
    }
}
