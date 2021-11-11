<?php

namespace rhertogh\Yii2Oauth2Server\components\authorization\base;

use rhertogh\Yii2Oauth2Server\interfaces\components\authorization\Oauth2ClientAuthorizationRequestInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\Oauth2UserInterface;
use rhertogh\Yii2Oauth2Server\Oauth2Module;
use yii\base\InvalidArgumentException;
use yii\base\InvalidCallException;
use yii\base\Model;

abstract class Oauth2BaseClientAuthorizationRequest extends Model implements Oauth2ClientAuthorizationRequestInterface
{
    /**
     * @var Oauth2Module|null
     */
    public $_module = null;

    /**
     * @var string|null
     */
    public $_authorizationStatus = null;

    /**
     * @var int|null
     */
    public $_clientIdentifier = null;

    /**
     * @var int|string|null
     */
    protected $_userIdentifier = null;

    /**
     * @var Oauth2UserInterface|null
     */
    protected $_userIdentity = null;

    /**
     * @var string|null
     */
    public $_authorizeUrl = null;

    /**
     * @var string|null
     */
    public $_redirectUri = null;

    /**
     * @var string[]
     */
    public $_requestedScopeIdentifiers = [];

    /**
     * @var string[]
     */
    public $_selectedScopeIdentifiers = [];

    /**
     * @var string|null
     */
    public $_grantType = null;

    /**
     * @var string[]
     */
    public $_prompts = [];

    /**
     * @var int|null
     */
    public $_maxAge = null;

    /**
     * @var bool
     */
    protected $_isCompleted = false;

    /**
     * @inheritDoc
     */
    public function getModule()
    {
        if (empty($this->_module)) {
            throw new InvalidCallException('Can not call getModule() before it\'s set.');
        }
        return $this->_module;
    }

    /**
     * @inheritDoc
     */
    public function setModule($module)
    {
        $this->_module = $module;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getAuthorizationStatus()
    {
        return $this->_authorizationStatus;
    }

    /**
     * @inheritDoc
     */
    public function setAuthorizationStatus($authorizationStatus)
    {
        if ($authorizationStatus !== null && !in_array($authorizationStatus, static::AUTHORIZATION_STATUSES)) {
            throw new InvalidArgumentException('$authorizationStatus must be null or exist in AUTHORIZATION_STATUSES.');
        }

        $this->_authorizationStatus = $authorizationStatus;
        $this->setCompleted(false);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getClientIdentifier()
    {
        return $this->_clientIdentifier;
    }

    /**
     * @inheritDoc
     */
    public function setClientIdentifier($clientIdentifier)
    {
        $this->_clientIdentifier = $clientIdentifier;
        $this->setCompleted(false);
        return $this;
    }

    /**
     * Get the user identifier.
     * @return string|int|null $userIdentifier
     * @since 1.0.0
     */
    protected function getUserIdentifier()
    {
        return $this->_userIdentifier;
    }

    /**
     * Set the user identifier.
     * @param string|int $userIdentifier
     * @return $this
     * @since 1.0.0
     */
    protected function setUserIdentifier($userIdentifier)
    {
        $this->_userIdentifier = $userIdentifier;
        if ($this->_userIdentity && $this->_userIdentity->getIdentifier() !== $userIdentifier) {
            $this->_userIdentity = null;
        }
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getUserIdentity()
    {
        if ($this->_userIdentity === null && $this->_userIdentifier !== null) {
            $this->_userIdentity = $this->getModule()->getUserRepository()->getUserEntityByIdentifier($this->_userIdentifier);
        }
        return $this->_userIdentity;
    }

    /**
     * @inheritDoc
     */
    public function setUserIdentity($userIdentity)
    {
        $this->_userIdentity = $userIdentity;
        $this->setUserIdentifier($userIdentity->getIdentifier());
        $this->setCompleted(false);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getAuthorizeUrl()
    {
        return $this->_authorizeUrl;
    }

    /**
     * @inheritDoc
     */
    public function setAuthorizeUrl($authorizeUrl)
    {
        $this->_authorizeUrl = $authorizeUrl;
        $this->setCompleted(false);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getRedirectUri()
    {
        return $this->_redirectUri;
    }
    /**
     * @inheritDoc
     */
    public function setRedirectUri($redirectUri)
    {
        $this->_redirectUri = $redirectUri;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getRequestedScopeIdentifiers()
    {
        return $this->_requestedScopeIdentifiers;
    }

    /**
     * @inheritDoc
     */
    public function setRequestedScopeIdentifiers($requestedScopeIdentifiers)
    {
        $this->_requestedScopeIdentifiers = $requestedScopeIdentifiers;
        $this->setCompleted(false);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getSelectedScopeIdentifiers()
    {
        return $this->_selectedScopeIdentifiers;
    }

    /**
     * @inheritDoc
     */
    public function setSelectedScopeIdentifiers($selectedScopeIdentifiers)
    {
        $this->_selectedScopeIdentifiers = $selectedScopeIdentifiers;
        $this->setCompleted(false);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getGrantType()
    {
        return $this->_grantType;
    }

    /**
     * @inheritDoc
     */
    public function setGrantType($grantType)
    {
        $this->_grantType = $grantType;
        $this->setCompleted(false);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getPrompts()
    {
        return $this->_prompts;
    }

    /**
     * @inheritDoc
     */
    public function setPrompts($prompts)
    {
        $this->_prompts = $prompts;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getMaxAge()
    {
        return $this->_maxAge;
    }

    /**
     * @inheritDoc
     */
    public function setMaxAge($maxAge)
    {
        $this->_maxAge = $maxAge;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function isApproved()
    {
        return $this->getAuthorizationStatus() === static::AUTHORIZATION_APPROVED;
    }

    /**
     * @inheritDoc
     */
    public function isCompleted()
    {
        return $this->_isCompleted;
    }

    /**
     * @param bool $isCompleted
     * @return $this
     */
    protected function setCompleted($isCompleted)
    {
        $this->_isCompleted = $isCompleted;
        return $this;
    }
}
