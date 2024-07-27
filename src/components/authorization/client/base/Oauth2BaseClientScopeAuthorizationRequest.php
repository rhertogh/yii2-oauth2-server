<?php

namespace rhertogh\Yii2Oauth2Server\components\authorization\client\base;

use rhertogh\Yii2Oauth2Server\interfaces\components\authorization\client\Oauth2ClientScopeAuthorizationRequestInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\Oauth2ScopeInterface;
use yii\base\Component;

abstract class Oauth2BaseClientScopeAuthorizationRequest extends Component implements
    Oauth2ClientScopeAuthorizationRequestInterface
{
    /**
     * @var Oauth2ScopeInterface|null
     */
    protected $_scope = null;

    /**
     * @var bool
     */
    protected bool $_isRequired = true;

    /**
     * @var bool
     */
    protected bool $_isAccepted = false;

    /**
     * @var bool
     */
    protected bool $_hasBeenRejectedBefore = false;

    /**
     * @inheritDoc
     */
    public function getScope(): Oauth2ScopeInterface
    {
        return $this->_scope;
    }

    /**
     * @inheritDoc
     */
    public function setScope(Oauth2ScopeInterface $scope)
    {
        $this->_scope = $scope;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getIsRequired(): bool
    {
        return $this->_isRequired;
    }

    /**
     * @inheritDoc
     */
    public function setIsRequired(bool $isRequired)
    {
        $this->_isRequired = $isRequired;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getIsAccepted(): bool
    {
        return $this->_isAccepted;
    }

    /**
     * @inheritDoc
     */
    public function setIsAccepted(bool $isAccepted)
    {
        $this->_isAccepted = $isAccepted;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getHasBeenRejectedBefore(): bool
    {
        return $this->_hasBeenRejectedBefore;
    }

    /**
     * @inheritDoc
     */
    public function setHasBeenRejectedBefore(bool $hasBeenRejectedBefore)
    {
        $this->_hasBeenRejectedBefore = $hasBeenRejectedBefore;
        return $this;
    }
}
