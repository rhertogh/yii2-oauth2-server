<?php

namespace rhertogh\Yii2Oauth2Server\components\openidconnect\scopes;

use rhertogh\Yii2Oauth2Server\interfaces\components\openidconnect\scope\Oauth2OidcScopeCollectionInterface;
use rhertogh\Yii2Oauth2Server\interfaces\components\openidconnect\scope\Oauth2OidcScopeInterface;
use Yii;
use yii\base\BaseObject;
use yii\base\InvalidArgumentException;
use yii\base\InvalidConfigException;
use yii\helpers\ArrayHelper;

class Oauth2OidcScopeCollection extends BaseObject implements Oauth2OidcScopeCollectionInterface
{
    /**
     * @var Oauth2OidcScopeInterface[]
     */
    protected $_oidcScopes = [];

    /**
     * @inheritDoc
     */
    public function getOidcScopes()
    {
        if (!array_key_exists(Oauth2OidcScopeInterface::OPENID_CONNECT_SCOPE_OPENID, $this->_oidcScopes)) {
            $this->_oidcScopes = array_merge( // ensure openid scope is always the first element
                [
                    Oauth2OidcScopeInterface::OPENID_CONNECT_SCOPE_OPENID =>
                        $this->getDefaultOidcScope(Oauth2OidcScopeInterface::OPENID_CONNECT_SCOPE_OPENID)
                ],
                $this->_oidcScopes
            );
        }
        return $this->_oidcScopes;
    }

    /**
     * @inheritDoc
     */
    public function setOidcScopes($oidcScopes)
    {
        $this->clearOidcScopes();
        return $this->addOidcScopes($oidcScopes);
    }

    /**
     * @inheritDoc
     */
    public function addOidcScopes($oidcScopes)
    {
        foreach ($oidcScopes as $scopeIdentifier => $scopeConfig) {
            if ($scopeConfig instanceof Oauth2OidcScopeInterface) {
                $this->addOidcScope($scopeConfig);
            } elseif (is_string($scopeConfig)) {
                $this->addOidcScope($scopeConfig);
            } elseif (is_array($scopeConfig)) {
                if (is_numeric($scopeIdentifier)) {
                    $this->addOidcScope($scopeConfig);
                } else {
                    $this->addOidcScope([
                        'identifier' => $scopeIdentifier,
                        'claims' => $scopeConfig,
                    ]);
                }
            } else {
                throw new InvalidArgumentException('Elements should be of type array, string or ' . Oauth2OidcScopeInterface::class);
            }
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function clearOidcScopes()
    {
        $this->_oidcScopes = [];
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getOidcScope($scopeIdentifier)
    {
        return $this->getOidcScopes()[$scopeIdentifier] ?? null;
    }

    /**
     * @inheritDoc
     */
    public function addOidcScope($oidcScope)
    {
        if (is_string($oidcScope)) {
            $oidcScope = $this->getDefaultOidcScope($oidcScope);
        } elseif (is_array($oidcScope)) {
            $oidcScope = Yii::createObject(ArrayHelper::merge(
                [
                    'class' => Oauth2OidcScopeInterface::class,
                ],
                $oidcScope
            ));
        }
        $identifier = $oidcScope->getIdentifier();
        if (empty($identifier)) {
            throw new InvalidArgumentException('Scope identifier must be set.');
        }
        $this->_oidcScopes[$identifier] = $oidcScope;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function removeOidcScope($scopeIdentifier)
    {
        unset($this->_oidcScopes[$scopeIdentifier]);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function hasOidcScope($scopeIdentifier)
    {
        return array_key_exists($scopeIdentifier, $this->getOidcScopes());
    }

    /**
     * @inheritDoc
     */
    public function getDefaultOidcScope($scopeIdentifier)
    {
        if (!in_array($scopeIdentifier, static::OPENID_CONNECT_DEFAULT_SCOPES)) {
            throw new InvalidArgumentException('Invalid $scopeName "' . $scopeIdentifier . '", it must be an OpenID Connect default claims scope (' . implode(', ', static::OPENID_CONNECT_DEFAULT_SCOPES) . ').');
        }

        return Yii::createObject([
            'class' => Oauth2OidcScopeInterface::class,
            'identifier' => $scopeIdentifier,
            'claims' => Oauth2OidcScopeInterface::OPENID_CONNECT_DEFAULT_SCOPE_CLAIMS[$scopeIdentifier],
        ]);
    }

    /**
     * @inheritDoc
     */
    public function getSupportedScopeAndClaimIdentifiers()
    {
        $result = [
            'scopeIdentifiers' => [],
            'claimIdentifiers' => [],
        ];

        foreach ($this->getOidcScopes() as $oidcScope) {
            $result['scopeIdentifiers'][] = $oidcScope->getIdentifier();
            $result['claimIdentifiers'] = array_merge($result['claimIdentifiers'], $oidcScope->getClaims());
        }
        $result['claimIdentifiers'] = array_keys($result['claimIdentifiers']);
        sort($result['claimIdentifiers']);

        return $result;
    }

    /**
     * @inheritDoc
     */
    public function getFilteredClaims($scopeIdentifiers)
    {
        $claims = [];
        foreach ($scopeIdentifiers as $scopeIdentifier) {
            $oidcScope = $this->getOidcScope($scopeIdentifier);
            if ($oidcScope) {
                $claims = array_merge($claims, $oidcScope->getClaims());
            }
        }

        ksort($claims);

        return $claims;
    }
}
