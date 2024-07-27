<?php

namespace rhertogh\Yii2Oauth2Server\components\authorization\client\base;

use rhertogh\Yii2Oauth2Server\components\authorization\base\Oauth2BaseAuthorizationRequest;
use rhertogh\Yii2Oauth2Server\interfaces\components\authorization\client\Oauth2ClientAuthorizationRequestInterface;
use yii\base\InvalidArgumentException;
use yii\base\InvalidCallException;
use yii\web\ForbiddenHttpException;

abstract class Oauth2BaseClientAuthorizationRequest extends Oauth2BaseAuthorizationRequest implements
    Oauth2ClientAuthorizationRequestInterface
{
    /**
     * @var string|null
     */
    public $_authorizeUrl = null;

    /**
     * @var string|null
     */
    public $_grantType = null;

    /**
     * @var string[]
     */
    public $_prompts = [];

    public $_createUserPromptProcessed = false;

    /**
     * @var int|null
     */
    public $_maxAge = null;

    /**
     * @var string[]
     */
    public $_requestedScopeIdentifiers = [];

    /**
     * @var string[]
     */
    public $_selectedScopeIdentifiers = [];

    /**
     * @var bool
     */
    protected $_userAuthenticatedBeforeRequest = false;

    /**
     * @var bool
     */
    protected $_authenticatedDuringRequest = false;

    public function __serialize()
    {
        return array_merge(parent::__serialize(), [
            '_authorizeUrl' => $this->_authorizeUrl,
            '_grantType' => $this->_grantType,
            '_prompts' => $this->_prompts,
            '_createUserPromptProcessed' => $this->_createUserPromptProcessed,
            '_maxAge' => $this->_maxAge,
            '_requestedScopeIdentifiers' => $this->_requestedScopeIdentifiers,
            '_selectedScopeIdentifiers' => $this->_selectedScopeIdentifiers,
            '_userAuthenticatedBeforeRequest' => $this->_userAuthenticatedBeforeRequest,
            '_authenticatedDuringRequest' => $this->_authenticatedDuringRequest,
        ]);
    }

    /**
     * @inheritDoc
     */
    public function getClient()
    {
        $clientIdentifier = $this->getClientIdentifier();
        if (empty($clientIdentifier)) {
            throw new InvalidCallException('Client identifier must be set.');
        }
        if (empty($this->_client) || $this->_client->getIdentifier() != $clientIdentifier) {
            $this->_client = $this->getModule()->getClientRepository()->getClientEntity($clientIdentifier);
            if (!$this->_client || !$this->_client->isEnabled()) {
                throw new ForbiddenHttpException('Client "' . $clientIdentifier . '" not found or disabled.');
            }
        }

        return $this->_client;
    }

    /**
     * @inheritDoc
     */
    public function setClient($client)
    {
        if (empty($client)) {
            throw new InvalidArgumentException('Client is required for Client Authorization Request.');
        }
        return parent::setClient($client);
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

    public function getCreateUserPromptProcessed()
    {
        return $this->_createUserPromptProcessed;
    }

    public function setCreateUserPromptProcessed($processed)
    {
        $this->_createUserPromptProcessed = $processed;
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
    public function setUserAuthenticatedBeforeRequest($authenticatedBeforeRequest)
    {
        $this->_userAuthenticatedBeforeRequest = $authenticatedBeforeRequest;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function wasUserAuthenticatedBeforeRequest()
    {
        return $this->_userAuthenticatedBeforeRequest;
    }

    /**
     * @inheritDoc
     */
    public function setUserAuthenticatedDuringRequest($authenticatedDuringRequest)
    {
        $this->_authenticatedDuringRequest = $authenticatedDuringRequest;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function wasUserAthenticatedDuringRequest()
    {
        return $this->_authenticatedDuringRequest;
    }
}
