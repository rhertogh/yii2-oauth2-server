<?php

namespace rhertogh\Yii2Oauth2Server\components\openidconnect\scopes;

use rhertogh\Yii2Oauth2Server\interfaces\components\openidconnect\scope\Oauth2OidcClaimInterface;
use rhertogh\Yii2Oauth2Server\interfaces\components\openidconnect\scope\Oauth2OidcScopeInterface;
use Yii;
use yii\base\BaseObject;
use yii\base\InvalidArgumentException;
use yii\base\InvalidCallException;
use yii\helpers\ArrayHelper;

class Oauth2OidcScope extends BaseObject implements Oauth2OidcScopeInterface
{
    /**
     * @var string|null
     */
    protected $_identifier;

    /**
     * @var array
     */
    protected $_claims;

    /**
     * @inheritDoc
     */
    public function getIdentifier()
    {
        if (empty($this->_identifier)) {
            throw new InvalidCallException('Trying to get scope identifier without it being set.');
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
    public function getClaims()
    {
        return $this->_claims;
    }

    /**
     * @inheritDoc
     */
    public function setClaims($claims)
    {
        $this->clearClaims();
        return $this->addClaims($claims);
    }

    /**
     * @inheritDoc
     */
    public function addClaims($claims)
    {
        foreach ($claims as $claimIdentifier => $claimConfig) {
            if ($claimConfig instanceof Oauth2OidcClaimInterface) {
                $this->addClaim($claimConfig);
            } elseif (is_string($claimConfig)) {
                if (is_numeric($claimIdentifier)) {
                    // e.g. ['claim_identifier'].
                    $this->addClaim($claimConfig);
                } else {
                    // e.g. ['claim_identifier' => 'determiner'].
                    $this->addClaim([
                        'identifier' => $claimIdentifier,
                        'determiner' => $claimConfig,
                    ]);
                }
            } elseif (is_array($claimConfig)) {
                if (is_numeric($claimIdentifier) && !array_key_exists('identifier', $claimConfig)) {
                    throw new InvalidArgumentException(
                        'If an element is an array it should either be declared as an associative element'
                            . ' or contain an "identifier" key.'
                    );
                }
                // e.g. ['claim' => [...]].
                $this->addClaim(ArrayHelper::merge(
                    [
                        'identifier' => $claimIdentifier,
                    ],
                    $claimConfig
                ));
            } else {
                throw new InvalidArgumentException(
                    'Elements must either be an array, string or a ' . Oauth2OidcClaimInterface::class
                );
            }
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function clearClaims()
    {
        $this->_claims = [];
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getClaim($claimIdentifier)
    {
        return $this->_claims[$claimIdentifier] ?? null;
    }

    /**
     * @inheritDoc
     */
    public function addClaim($claim)
    {
        if (is_string($claim)) {
            $claim = ['identifier' => $claim];
        }
        if (is_array($claim)) {
            $claim = Yii::createObject(ArrayHelper::merge(
                [
                    'class' => Oauth2OidcClaimInterface::class,
                ],
                $claim
            ));
        }

        $identifier = $claim->getIdentifier();
        $this->_claims[$identifier] = $claim;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function removeClaim($claimIdentifier)
    {
        unset($this->_claims[$claimIdentifier]);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function hasClaim($claimIdentifier)
    {
        return array_key_exists($claimIdentifier, $this->_claims);
    }
}
