<?php


namespace rhertogh\Yii2Oauth2Server\interfaces\models;


use League\OAuth2\Server\Entities\ScopeEntityInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\base\Oauth2ActiveRecordIdInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\queries\Oauth2ScopeQueryInterface;
use yii\db\ActiveQuery;

interface Oauth2ScopeInterface extends
    Oauth2ActiveRecordIdInterface,
    ScopeEntityInterface
{
    /**
     * Applied by Default "No": Do not apply the scope automatically.
     * @since 1.0.0
     */
    const APPLIED_BY_DEFAULT_NO = 0;
    /**
     * Applied by Default "Yes": Apply the scope automatically (without the client requesting it via the
     * authorization request's scope parameter), the user still has to approve the scope.
     * @since 1.0.0
     */
    const APPLIED_BY_DEFAULT_CONFIRM = 1;
    /**
     * Applied by Default "Automatically": Apply the scope automatically (without the client requesting it via the
     * authorization request's scope parameter), the user will *not* be prompted to approve the scope.
     * @since 1.0.0
     */
    const APPLIED_BY_DEFAULT_AUTOMATICALLY = 2;
    /**
     * @since 1.0.0
     */
    const APPLIED_BY_DEFAULT_OPTIONS = [
        self::APPLIED_BY_DEFAULT_NO,
        self::APPLIED_BY_DEFAULT_CONFIRM,
        self::APPLIED_BY_DEFAULT_AUTOMATICALLY,
    ];

    /**
     * @inheritDoc
     * @return Oauth2ScopeQueryInterface|ActiveQuery
     */
    public static function find();

    /**
     * Get the description for the scope
     * @return mixed
     * @since 1.0.0
     */
    public function getDescription();

    /**
     * Get ClientScope relation for a specific client.
     * @param int $clientId
     * @return Oauth2ClientScopeInterface
     * @since 1.0.0
     */
    public function getClientScope($clientId);

    /**
     * Returns if the scope is applied by default for all clients. If so, the scope will be added without a client
     * requesting it via the authorization request's scope parameter. This setting can be overwritten in the
     * ClientScope relation.
     * Note: Whether a scope is applied also depends on the client's `getScopeAccess()` setting.
     * @return int
     * @see \rhertogh\Yii2Oauth2Server\interfaces\models\Oauth2ClientInterface::getScopeAccess()
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
