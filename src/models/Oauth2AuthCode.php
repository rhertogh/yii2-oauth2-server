<?php

namespace rhertogh\Yii2Oauth2Server\models;

use DateTimeImmutable;
use rhertogh\Yii2Oauth2Server\interfaces\models\Oauth2AuthCodeInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\Oauth2AuthCodeScopeInterface;
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
 */
class Oauth2AuthCode extends base\Oauth2AuthCode implements Oauth2AuthCodeInterface
{
    use Oauth2ActiveRecordIdTrait;
    use Oauth2EntityIdentifierTrait;
    use Oauth2UserIdentifierTrait;
    use Oauth2TokenTrait;
    use Oauth2ExpiryDateTimeTrait;
    use Oauth2ScopesRelationTrait;
    use Oauth2ClientRelationTrait {
        __set as clientRelationSetter;
    }

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
    public function __set($name, $value)
    {
        $this->clientRelationSetter($name, $value); // wrapper function to ensure the __set function of the Oauth2ClientRelationTrait is never overwritten
    }

    /**
     * @inheritDoc
     */
    public function getRedirectUri()
    {
        return $this->redirect_uri;
    }

    /**
     * @inheritDoc
     */
    public function setRedirectUri($uri)
    {
        $this->redirect_uri = $uri;
    }

    /**
     * @inheritDoc
     */
    public function getScopesRelationClassName()
    {
        return Oauth2AuthCodeScopeInterface::class;
    }
}
