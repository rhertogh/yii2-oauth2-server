<?php

namespace rhertogh\Yii2Oauth2Server\components\authorization;

use rhertogh\Yii2Oauth2Server\components\authorization\base\Oauth2BaseClientAuthorizationRequest;
use rhertogh\Yii2Oauth2Server\helpers\DiHelper;
use rhertogh\Yii2Oauth2Server\helpers\UrlHelper;
use rhertogh\Yii2Oauth2Server\interfaces\components\authorization\Oauth2ScopeAuthorizationRequestInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\Oauth2ClientInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\Oauth2ScopeInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\Oauth2UserClientInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\Oauth2UserClientScopeInterface;
use rhertogh\Yii2Oauth2Server\models\Oauth2Scope;
use Yii;
use yii\base\InvalidCallException;
use yii\helpers\StringHelper;

class Oauth2ClientAuthorizationRequest extends Oauth2BaseClientAuthorizationRequest
{
    /**
     * @var string|null
     */
    protected $_requestId = null;

    /**
     * @var string|null
     */
    protected $_state = null;

    /**
     * @var bool
     */
    protected $_userAuthenticatedBeforeRequest = false;

    /**
     * @var bool
     */
    protected $_authenticatedDuringRequest = false;

    /**
     * @var Oauth2ClientInterface|null
     */
    protected $_client = null;

    /**
     * @var Oauth2ScopeAuthorizationRequestInterface[]|null
     */
    protected $_scopeAuthorizationRequests = null;

    /**
     * @var Oauth2ScopeInterface[]|null
     */
    protected $_scopesAppliedByDefaultWithoutConfirm = null;

    /**
     * @inheritDoc
     */
    public function __serialize()
    {
        return [
            '_requestId' => $this->_requestId,
            '_clientIdentifier' => $this->_clientIdentifier,
            '_state' => $this->_state,
            '_userIdentifier' => $this->_userIdentifier,
            '_userAuthenticatedBeforeRequest' => $this->_userAuthenticatedBeforeRequest,
            '_authenticatedDuringRequest' => $this->_authenticatedDuringRequest,
            '_authorizeUrl' => $this->_authorizeUrl,
            '_requestedScopeIdentifiers' => $this->_requestedScopeIdentifiers,
            '_grantType' => $this->_grantType,
            '_prompts' => $this->_prompts,
            '_maxAge' => $this->_maxAge,
            '_selectedScopeIdentifiers' => $this->_selectedScopeIdentifiers,
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
    public function rules()
    {
        return [
            [['selectedScopeIdentifiers'], 'each', 'rule' => ['string']],
            [['authorizationStatus'], 'required'],
            [['authorizationStatus'], 'in', 'range' => [static::AUTHORIZATION_APPROVED, static::AUTHORIZATION_DENIED]],
        ];
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
    public function getState()
    {
        return $this->_state;
    }

    /**
     * @inheritDoc
     */
    public function setState($state)
    {
        $this->_state = $state;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setClientIdentifier($clientIdentifier)
    {
        if ($this->_client && $this->_client->getIdentifier() !== $clientIdentifier) {
            $this->_client = null;
        }

        $this->_scopeAuthorizationRequests = null;
        $this->_scopesAppliedByDefaultWithoutConfirm = null;

        return parent::setClientIdentifier($clientIdentifier);
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
        }

        return $this->_client;
    }

    /**
     * @inheritDoc
     */
    public function setClient($client)
    {
        $this->_client = $client;
        $this->setClientIdentifier($client->getIdentifier());
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setUserIdentity($userIdentity)
    {
        if ($this->getUserIdentifier() !== $userIdentity->getIdentifier()) {
            $this->_scopeAuthorizationRequests = null;
            $this->_scopesAppliedByDefaultWithoutConfirm = null;
        }

        return parent::setUserIdentity($userIdentity);
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

    /**
     * @inheritDoc
     */
    public function setRequestedScopeIdentifiers($requestedScopeIdentifiers)
    {
        $this->_scopeAuthorizationRequests = null;
        $this->_scopesAppliedByDefaultWithoutConfirm = null;
        return parent::setRequestedScopeIdentifiers($requestedScopeIdentifiers);
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
     * @inheritDoc
     */
    public function isAuthorizationNeeded()
    {
        // Prevent Client Impersonation (https://datatracker.ietf.org/doc/html/rfc6749#section-10.2).
        if (!$this->isClientIdentifiable()) {
            return true; // Always require authorization of non-identifiable clients.
        }

        $isScopeAuthorizationNeeded = $this->isScopeAuthorizationNeeded();

        return ($this->isClientAuthorizationNeeded() && !$this->getClient()->skipAuthorizationIfScopeIsAllowed())
            || $isScopeAuthorizationNeeded;
    }

    /**
     * @inheritDoc
     */
    public function isClientAuthorizationNeeded()
    {
        /** @var Oauth2UserClientInterface $userClientClass */
        $userClientClass = DiHelper::getValidatedClassName(Oauth2UserClientInterface::class);

        return !$userClientClass::find()
            ->andWhere([
                'user_id' => $this->getUserIdentifier(),
                'client_id' => $this->getClient()->getPrimaryKey(),
                'enabled' => 1,
            ])
            ->exists();
    }

    /**
     * @inheritDoc
     */
    public function isScopeAuthorizationNeeded()
    {
        foreach ($this->getScopeAuthorizationRequests() as $scopeAuthorizationRequest) {
            if (!$scopeAuthorizationRequest->getIsAccepted()) {
                return true;
            }
        }
        return false;
    }

    /**
     * Get all the Scope Authorization Requests for this Client Authorization Request.
     * @return Oauth2ScopeAuthorizationRequestInterface[]
     * @throws \yii\base\InvalidConfigException
     * @see determineScopeAuthorization()
     * @since 1.0.0
     */
    protected function getScopeAuthorizationRequests()
    {
        if ($this->_scopeAuthorizationRequests === null) {
            $this->determineScopeAuthorization();
        }

        return $this->_scopeAuthorizationRequests;
    }

    /**
     * Calculate the Scope Authorization Requests for this Client Authorization Request.
     * @throws \yii\base\InvalidConfigException
     * @since 1.0.0
     */
    protected function determineScopeAuthorization()
    {
        $scopeAuthorizationRequests = [];
        $scopesAppliedByDefaultWithoutConfirm = [];

        $client = $this->getClient();
        $requestedScopeIdentifiers = $this->getRequestedScopeIdentifiers();
        $allowedScopes = $client->getAllowedScopes($requestedScopeIdentifiers);

        /** @var Oauth2UserClientScopeInterface $userClientScopeClass */
        $userClientScopeClass = DiHelper::getValidatedClassName(Oauth2UserClientScopeInterface::class);

        /** @var Oauth2UserClientScopeInterface[] $userClientScopes */
        $userClientScopes = $userClientScopeClass::find()
            ->andWhere([
                'user_id' => $this->getUserIdentity()->getIdentifier(),
                'client_id' => $client->getPrimaryKey(),
                'scope_id' => array_map(fn($scope) => $scope->getPrimaryKey(), $allowedScopes)
            ])
            ->indexBy('scope_id')
            ->all();

        foreach ($allowedScopes as $scope) {
            $clientScope = $scope->getClientScope($client->getPrimaryKey());
            $appliedByDefault = ($clientScope ? $clientScope->getAppliedByDefault() : null)
                ?? $scope->getAppliedByDefault();

            $scopeIdentifier = $scope->getIdentifier();
            if (
                ($appliedByDefault === Oauth2ScopeInterface::APPLIED_BY_DEFAULT_AUTOMATICALLY)
                || (
                    $appliedByDefault === Oauth2ScopeInterface::APPLIED_BY_DEFAULT_IF_REQUESTED
                    && in_array($scopeIdentifier, $requestedScopeIdentifiers)
                )
            ) {
                unset($scopeAuthorizationRequests[$scopeIdentifier]);
                $scopesAppliedByDefaultWithoutConfirm[$scopeIdentifier] = $scope;
            } elseif ($appliedByDefault !== Oauth2ScopeInterface::APPLIED_BY_DEFAULT_IF_REQUESTED) {
                $isRequired = ($clientScope ? $clientScope->getRequiredOnAuthorization() : null)
                    ?? $scope->getRequiredOnAuthorization();
                $userClientScope = $userClientScopes[$scope->getPrimaryKey()] ?? null;

                /** @var Oauth2ScopeAuthorizationRequestInterface $scopeAuthorizationRequest */
                $scopeAuthorizationRequest = Yii::createObject(Oauth2ScopeAuthorizationRequestInterface::class);
                $scopeAuthorizationRequest
                    ->setScope($scope)
                    ->setIsRequired($isRequired)
                    ->setIsAccepted($userClientScope && $userClientScope->isEnabled())
                    ->setHasBeenRejectedBefore($userClientScope && !$userClientScope->isEnabled());

                $scopeAuthorizationRequests[$scopeIdentifier] = $scopeAuthorizationRequest;
            }
        }

        $this->_scopeAuthorizationRequests = $scopeAuthorizationRequests;
        $this->_scopesAppliedByDefaultWithoutConfirm = $scopesAppliedByDefaultWithoutConfirm;
    }

    /**
     * @inheritDoc
     */
    public function getApprovalPendingScopes()
    {
        $pendingApprovalRequests = [];
        foreach ($this->getScopeAuthorizationRequests() as $scopeIdentifier => $scopeAuthorizationRequest) {
            if (!$scopeAuthorizationRequest->getIsAccepted()) {
                $pendingApprovalRequests[$scopeIdentifier] = $scopeAuthorizationRequest;
            }
        }
        return $pendingApprovalRequests;
    }

    /**
     * @inheritDoc
     */
    public function getPreviouslyApprovedScopes()
    {
        $previouslyApprovedScopes = [];
        foreach ($this->getScopeAuthorizationRequests() as $scopeIdentifier => $scopeAuthorizationRequest) {
            if ($scopeAuthorizationRequest->getIsAccepted()) {
                $previouslyApprovedScopes[$scopeIdentifier] = $scopeAuthorizationRequest;
            }
        }
        return $previouslyApprovedScopes;
    }

    /**
     * @inheritDoc
     */
    public function getScopesAppliedByDefaultWithoutConfirm()
    {
        if ($this->_scopesAppliedByDefaultWithoutConfirm === null) {
            $this->determineScopeAuthorization();
        }

        return $this->_scopesAppliedByDefaultWithoutConfirm;
    }

    /**
     * @inheritdoc
     */
    public function isAuthorizationAllowed()
    {
        return
            ($this->getClient() !== null)
            && ($this->getGrantType() !== null)
            && (
                $this->getClient()->endUsersMayAuthorizeClient()
                && $this->getUserIdentity() !== null
            );
    }

    /**
     * @inheritDoc
     */
    public function processAuthorization()
    {
        if ($this->getAuthorizationStatus() === null) {
            throw new InvalidCallException('Unable to process authorization without authorization status.');
        }

        $userId = $this->getUserIdentifier();
        $clientId = $this->getClient()->getPrimaryKey();

        /** @var Oauth2UserClientInterface $userClientClass */
        $userClientClass = DiHelper::getValidatedClassName(Oauth2UserClientInterface::class);

        $userClient = $userClientClass::findOrCreate([
            'user_id' => $userId,
            'client_id' => $clientId,
        ]);

        if ($userClient->getIsNewRecord()) {
            if ($this->isApproved()) {
                $userClient->setEnabled(true);
                $userClient->persist();
            }
        } elseif ($userClient->isEnabled() != $this->isApproved()) {
            $userClient->setEnabled($this->isApproved());
            $userClient->persist();
        }

        if ($this->isApproved()) {

            /** @var Oauth2UserClientScopeInterface $userClientScopeClass */
            $userClientScopeClass = DiHelper::getValidatedClassName(Oauth2UserClientScopeInterface::class);

            /** @var Oauth2UserClientScopeInterface[] $userClientScopes */
            $userClientScopes = $userClientScopeClass::find()
                ->andWhere([
                    'user_id' => $userId,
                    'client_id' => $clientId,
                ])
                ->indexBy('scope_id')
                ->all();


            $scopeAuthorizationRequests = $this->getScopeAuthorizationRequests();
            $selectedScopeIdentifiers = $this->getSelectedScopeIdentifiers();

            $scopeAcceptance = [
                0 => [],
                1 => [],
            ];
            foreach ($scopeAuthorizationRequests as $scopeAuthorizationRequest) {
                $scope = $scopeAuthorizationRequest->getScope();
                $isAccepted = in_array($scope->getIdentifier(), $selectedScopeIdentifiers);
                $scopeAcceptance[$isAccepted][] = $scope;
            }

            foreach ($scopeAcceptance as $isAccepted => $scopes) {
                foreach ($scopes as $scope) {
                    $scopeId = $scope->getPrimaryKey();
                    /** @var Oauth2UserClientScopeInterface $userClientScope */
                    $userClientScope = $userClientScopes[$scopeId] ?? Yii::createObject([
                            'class' => Oauth2UserClientScopeInterface::class,
                            'user_id' => $userId,
                            'client_id' => $clientId,
                            'scope_id' => $scopeId,
                        ]);
                    $userClientScope->setEnabled($isAccepted);
                    $userClientScope->persist();
                }
            }
        }

        $this->setCompleted(true);
    }

    /**
     * @inheritDoc
     */
    public function getAuthorizationRequestUrl()
    {
        return UrlHelper::addQueryParams(
            $this->getAuthorizeUrl(),
            [
                'clientAuthorizationRequestId' => $this->getRequestId()
            ]
        );
    }
}
