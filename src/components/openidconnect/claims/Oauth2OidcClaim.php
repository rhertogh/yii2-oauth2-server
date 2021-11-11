<?php
namespace rhertogh\Yii2Oauth2Server\components\openidconnect\claims;

use rhertogh\Yii2Oauth2Server\interfaces\components\openidconnect\scope\Oauth2OidcClaimInterface;
use yii\base\BaseObject;
use yii\base\InvalidCallException;

class Oauth2OidcClaim extends BaseObject implements Oauth2OidcClaimInterface
{
    /**
     * @var string
     */
    protected $_identifier;

    /**
     * @var string|null
     */
    protected $_determiner;

    /**
     * @var mixed
     */
    protected $_defaultValue;

    /**
     * @inheritDoc
     */
    public function getIdentifier()
    {
        if (empty($this->_identifier)) {
            throw new InvalidCallException('Trying to get claim identifier without it being set.');
        }
        return $this->_identifier;
    }

    /**
     * @inheritDoc
     */
    public function setIdentifier($identifier)
    {
        $this->_identifier = $identifier;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getDeterminer()
    {
        return $this->_determiner ?? $this->getIdentifier();
    }

    /**
     * @inheritDoc
     */
    public function setDeterminer($determiner)
    {
        $this->_determiner = $determiner;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getDefaultValue()
    {
        return $this->_defaultValue;
    }

    /**
     * @inheritDoc
     */
    public function setDefaultValue($defaultValue)
    {
        $this->_defaultValue = $defaultValue;
        return $this;
    }

}
