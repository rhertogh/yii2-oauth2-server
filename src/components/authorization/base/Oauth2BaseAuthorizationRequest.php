<?php

namespace rhertogh\Yii2Oauth2Server\components\authorization\base;

use rhertogh\Yii2Oauth2Server\interfaces\components\authorization\base\Oauth2BaseAuthorizationRequestInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\external\user\Oauth2UserInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\Oauth2ClientInterface;
use rhertogh\Yii2Oauth2Server\Oauth2Module;
use yii\base\InvalidArgumentException;
use yii\base\InvalidCallException;
use yii\base\Model;
use yii\helpers\StringHelper;

abstract class Oauth2BaseAuthorizationRequest extends Model implements Oauth2BaseAuthorizationRequestInterface
{
    /**
     * @var Oauth2Module|null
     */
    public $_module = null;

    /**
     * @var string|null
     */
    protected $_requestId = null;

    /**
     * @var int|null
     */
    public $_clientIdentifier = null;

    /**
     * @var Oauth2ClientInterface|null
     */
    protected $_client = null;

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
    public $_redirectUri = null;

    /**
     * @var string|null
     */
    public $_authorizationStatus = null;

    /**
     * @var bool
     */
    protected $_isCompleted = false;

    public static function getPossibleAuthorizationStatuses()
    {
        return [
            Oauth2BaseAuthorizationRequestInterface::AUTHORIZATION_APPROVED,
            Oauth2BaseAuthorizationRequestInterface::AUTHORIZATION_DENIED,
        ];
    }

    /**
     * @inheritDoc
     */
    public function __serialize()
    {
        return [
            '_requestId' => $this->_requestId,
            '_clientIdentifier' => $this->_clientIdentifier,
            '_userIdentifier' => $this->_userIdentifier,
            '_redirectUri' => $this->_redirectUri,
            '_authorizationStatus' => $this->_authorizationStatus,
            '_isCompleted' => $this->_isCompleted,
        ];
    }

    /**
     * @inheritDoc
     */
    public function __unserialize($data)
    {
        foreach ($data as $name => $value) {
            $this->$name = $value;
        }
    }

    /**
     * @inheritDoc
     */
    public function init()
    {
        parent::init();
        $this->_requestId = \Yii::$app->security->generateRandomString(128);
    }

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
    public function getRequestId()
    {
        return $this->_requestId;
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
        if ($this->_client && $this->_client->getIdentifier() !== $clientIdentifier) {
            $this->_client = null;
        }

        $this->_clientIdentifier = $clientIdentifier;
        $this->setCompleted(false);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getClient()
    {
        $clientIdentifier = $this->getClientIdentifier();
        if (empty($clientIdentifier)) {
            return null;
        }

        if (empty($this->_client) || $this->_client->getIdentifier() != $clientIdentifier) {
            $this->_client = $this->getModule()->getClientRepository()->getClientEntity($clientIdentifier);
        }

        return $this->_client;
    }

    /**
     * @inheritDoc
     */
    public function setClient($client)
    {
        $this->_client = $client;
        $this->setClientIdentifier($client ? $client->getIdentifier() : null);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function isClientIdentifiable()
    {
        return
            $this->getClient()->isConfidential()
            || StringHelper::startsWith((string)$this->getRedirectUri(), 'https://');
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
            $this->_userIdentity = $this->getModule()
                ->getUserRepository()
                ->getUserEntityByIdentifier($this->_userIdentifier);
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
    public function getAuthorizationStatus()
    {
        return $this->_authorizationStatus;
    }

    /**
     * @inheritDoc
     */
    public function setAuthorizationStatus($authorizationStatus)
    {
        if ($authorizationStatus !== null && !in_array($authorizationStatus, $this->getPossibleAuthorizationStatuses())) {
            throw new InvalidArgumentException('$authorizationStatus must be null or exist in the return value of `getPossibleAuthorizationStatuses()`.');
        }

        $this->_authorizationStatus = $authorizationStatus;
        $this->setCompleted(false);
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
     * @param bool $isCompleted
     * @return $this
     */
    protected function setCompleted($isCompleted)
    {
        $this->_isCompleted = $isCompleted;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function isCompleted()
    {
        return $this->_isCompleted;
    }

    /**
     * @inheritDoc
     */
    public function rules()
    {
        return [
            [['authorizationStatus'], 'required'],
            [['authorizationStatus'], 'in', 'range' => [static::AUTHORIZATION_APPROVED, static::AUTHORIZATION_DENIED]],
        ];
    }
}
