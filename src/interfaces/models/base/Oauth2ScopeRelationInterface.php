<?php

namespace rhertogh\Yii2Oauth2Server\interfaces\models\base;

use rhertogh\Yii2Oauth2Server\interfaces\models\Oauth2ScopeInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\queries\Oauth2ScopeQueryInterface;

interface Oauth2ScopeRelationInterface
{
    /**
     * Set the related scopes.
     * @param Oauth2ScopeInterface[] $scopes
     * @since 1.0.0
     */
    public function setScopes($scopes);

    /**
     * Get the scopes relation.
     * @return Oauth2ScopeQueryInterface
     * @since 1.0.0
     */
    public function getScopesRelation();

    /**
     * Get the class name for the scopes relation.
     * @return Oauth2ActiveRecordInterface
     * @since 1.0.0
     */
    public function getScopesRelationClassName();

    # region TokenInterface methods (overwritten for type covariance)
    /**
     * @inheritDoc
     * @return Oauth2ScopeInterface[]
     */
    public function getScopes();
    # endregion
}
