<?php

namespace rhertogh\Yii2Oauth2Server\models\traits;

use League\OAuth2\Server\Entities\ScopeEntityInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\Oauth2ScopeInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\queries\Oauth2ScopeQueryInterface;
use yii\base\InvalidConfigException;

/**
 * @property Oauth2ScopeInterface[] $scopesRelation
 */
trait Oauth2ScopesRelationTrait
{
    abstract public function populateRelation($name, $records);

    /**
     * Wrapper for parent's getScopes() relation to avoid name conflicts
     * @inheritDoc
     */
    public function getScopesRelation()
    {
        return parent::getScopes();
    }

    /**
     * @inheritDoc
     */
    public function setScopes($scopes)
    {
        foreach ($scopes as $scope) {
            if (!($scope instanceof Oauth2ScopeInterface)) {
                throw new InvalidConfigException(get_class($scope) . ' must implement ' . Oauth2ScopeInterface::class);
            }
        }
        $this->populateRelation('scopesRelation', $scopes);
    }

    /**
     * Get the scopes for this model.
     * @return Oauth2ScopeInterface[]
     * @since 1.0.0
     */
    public function getScopes()
    {
        return $this->scopesRelation;
    }

    /**
     * @inheritDoc
     */
    public function addScope(ScopeEntityInterface $scope)
    {
        $scopes = $this->scopesRelation;
        $scopes[] = $scope;
        $this->setScopes($scopes);
    }
}
