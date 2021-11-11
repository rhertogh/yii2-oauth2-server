<?php

namespace rhertogh\Yii2Oauth2Server\interfaces\components\authorization;

use rhertogh\Yii2Oauth2Server\interfaces\models\Oauth2ScopeInterface;

interface Oauth2ScopeAuthorizationRequestInterface
{
    /**
     * Get the scope for this Scope Authorization Request.
     * @return Oauth2ScopeInterface
     * @since 1.0.0
     */
    public function getScope(): Oauth2ScopeInterface;

    /**
     * Set the scope for this Scope Authorization Request.
     * @param Oauth2ScopeInterface $scope
     * @return $this
     * @since 1.0.0
     */
    public function setScope(Oauth2ScopeInterface $scope);

    /**
     * Get if the approval of this scope is required.
     * @return bool
     * @since 1.0.0
     */
    public function getIsRequired(): bool;

    /**
     * Set if the approval of this scope is required.
     * @param bool $isRequired
     * @return $this
     * @since 1.0.0
     */
    public function setIsRequired(bool $isRequired);

    /**
     * Get if the user approved this scope.
     * @return bool
     * @since 1.0.0
     */
    public function getIsAccepted(): bool;

    /**
     * Set if the user approved this scope.
     * @param bool $isAccepted
     * @return $this
     * @since 1.0.0
     */
    public function setIsAccepted(bool $isAccepted);

    /**
     * Get if the user has rejected this scope before.
     * @return bool
     * @since 1.0.0
     */
    public function getHasBeenRejectedBefore(): bool;

    /**
     * Set if the user has rejected this scope before.
     * @param bool $hasBeenRejectedBefore
     * @return $this
     * @since 1.0.0
     */
    public function setHasBeenRejectedBefore(bool $hasBeenRejectedBefore);
}
