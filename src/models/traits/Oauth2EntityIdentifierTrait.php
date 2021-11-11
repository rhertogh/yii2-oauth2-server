<?php

namespace rhertogh\Yii2Oauth2Server\models\traits;

use rhertogh\Yii2Oauth2Server\interfaces\models\base\Oauth2ActiveRecordInterface;
use yii\db\ActiveRecordInterface;

trait Oauth2EntityIdentifierTrait
{
    ////////////////////////
    /// Static Functions ///
    ////////////////////////

    /**
     * @inheritDoc
     */
    public static function findByIdentifier($identifier)
    {
        return static::findOne(['identifier' => $identifier]);
    }

    /////////////////////////
    /// Getters & Setters ///
    /////////////////////////

    /**
     * @inheritDoc
     */
    public function getIdentifier()
    {
        /** @var ActiveRecordInterface $this */
        return $this->getAttribute('identifier');
    }

    /**
     * @inheritDoc
     */
    public function setIdentifier($identifier)
    {
        /** @var ActiveRecordInterface $this */
        $this->setAttribute('identifier', $identifier);
    }

    /////////////////////////
    /// Getters & Setters ///
    /////////////////////////

    /**
     * @inheritDoc
     */
    public function identifierExists()
    {
        /** @var Oauth2ActiveRecordInterface|string $modelClass */
        $modelClass = get_class($this);
        return $modelClass::find()->andWhere(['identifier' => $this->getIdentifier()])->exists();
    }
}
