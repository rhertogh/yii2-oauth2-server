<?php

namespace rhertogh\Yii2Oauth2Server\interfaces\models;

use rhertogh\Yii2Oauth2Server\interfaces\models\base\Oauth2ActiveRecordInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\queries\Oauth2ClientScopeQueryInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\queries\Oauth2ScopeQueryInterface;
use yii\db\ActiveQuery;

interface Oauth2ClientScopeInterface extends Oauth2ActiveRecordInterface
{
    /**
     * @inheritDoc
     * @return Oauth2ClientScopeQueryInterface|ActiveQuery
     */
    public static function find();

    /**
     * Returns if the scope is applied by default for the client. If so, the scope will be added without the client
     * requesting it via the authorization request's scope parameter.
     * @return int|null
     * @see \rhertogh\Yii2Oauth2Server\interfaces\models\Oauth2ScopeInterface::APPLIED_BY_DEFAULT_OPTIONS
     * @since 1.0.0
     */
    public function getAppliedByDefault();

    /**
     * Is the scope required during client/scope authorization. If so, the user can't choose to accept or decline the
     * specific scope during the authorization and the scope will always be accepted when the user approves the client.
     * @return bool|null
     * @since 1.0.0
     */
    public function getRequiredOnAuthorization();

    /**
     * Get the scope for this model.
     * @return Oauth2ScopeQueryInterface|ActiveQuery
     * @since 1.0.0
     */
    public function getScope();
}
