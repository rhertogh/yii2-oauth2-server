<?php

namespace rhertogh\Yii2Oauth2Server\interfaces\models;

use League\OAuth2\Server\Entities\ScopeEntityInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\base\Oauth2ActiveRecordInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\queries\Oauth2ScopeQueryInterface;

interface Oauth2ScopeInterface extends
    Oauth2ActiveRecordInterface,
    ScopeEntityInterface
{
    /**
     * Applied by Default "No": Do not apply the scope automatically. If the client requests the scope the user
     * will be prompted for approval.
     * @since 1.0.0
     */
    public const APPLIED_BY_DEFAULT_NO = 0;

    /**
     * Applied by Default "Confirm": Apply the scope automatically (without the client requesting it via the
     * authorization request's scope parameter), the user still has to approve the scope.
     * @since 1.0.0
     */
    public const APPLIED_BY_DEFAULT_CONFIRM = 1;

    /**
     * Applied by Default "Automatically": Apply the scope automatically (without the client requesting it via the
     * authorization request's scope parameter), the user will *not* be prompted to approve the scope.
     * @since 1.0.0
     */
    public const APPLIED_BY_DEFAULT_AUTOMATICALLY = 2;

    /**
     * Applied if requested: Apply the scope if the client requests it via the authorization request's scope parameter,
     * the user will *not* be prompted to approve the scope.
     * @since 1.0.0
     */
    public const APPLIED_BY_DEFAULT_IF_REQUESTED = 3;

    /**
     * @since 1.0.0
     */
    public const APPLIED_BY_DEFAULT_OPTIONS = [
        self::APPLIED_BY_DEFAULT_NO,
        self::APPLIED_BY_DEFAULT_CONFIRM,
        self::APPLIED_BY_DEFAULT_AUTOMATICALLY,
        self::APPLIED_BY_DEFAULT_IF_REQUESTED,
    ];

    /**
     * @inheritDoc
     * @return Oauth2ScopeQueryInterface
     */
    public static function find();

    /**
     * Get the description for the scope
     * @return string|null
     * @since 1.0.0
     */
    public function getDescription();

    /**
     * Get the message to show to the end-user during client authorization for this scope.
     * If this value is `null`, the description will be used.
     * @return string|null
     * @since 1.0.0
     * @see getDescription()
     */
    public function getAuthorizationMessage();

    /**
     * Get ClientScope relation for a specific client.
     * @param int $clientId
     * @return Oauth2ClientScopeInterface|null
     * @since 1.0.0
     */
    public function getClientScope($clientId);

    /**
     * Returns if the scope is applied by default (but only if it is available for the client).
     * If so, the scope will be added without a client requesting it via the authorization request's scope parameter.
     * This setting can be overwritten in the ClientScope relation.
     *
     * Note: Whether a scope can be applied foremost depends on its availability for a Client. It must either be linked
     * via the `oauth2_client_scope` table or the Client must explicitly allow all scopes via its
     * `getAllowGenericScopes()` setting (or `oauth2_client.allow_generic_scopes` database column).
     *
     * @return int
     * @see \rhertogh\Yii2Oauth2Server\interfaces\models\Oauth2ClientScopeInterface
     * @see \rhertogh\Yii2Oauth2Server\interfaces\models\Oauth2ClientInterface::getAllowGenericScopes()
     * @see APPLIED_BY_DEFAULT_OPTIONS
     * @since 1.0.0
     */
    public function getAppliedByDefault();

    /**
     * Is the scope required during client/scope authorization. If so, the user can't choose to accept or decline the
     * specific scope during the authorization and the scope will always be accepted when the user approves the client.
     * This setting can be overwritten in the ClientScope relation.
     * @return bool|null
     * @since 1.0.0
     */
    public function getRequiredOnAuthorization();
}
