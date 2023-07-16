<?php

namespace rhertogh\Yii2Oauth2Server\interfaces\models\external\user;

use League\OAuth2\Server\Entities\UserEntityInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\Oauth2ClientInterface;
use yii\db\TableSchema;
use yii\web\IdentityInterface;

interface Oauth2UserInterface extends
    UserEntityInterface
{
    /**
     * Get the table schema for the model. If the model does not have a table this method should return `null`.
     * @return TableSchema|null
     * @since 1.0.0
     */
    public static function getTableSchema();

    /**
     * Finds an identity by the given ID.
     * @param string|int $id the ID to be looked for
     * @return Oauth2UserInterface|IdentityInterface|null the identity object that matches the given ID.
     * Null should be returned if such an identity cannot be found
     * or the identity is not in an active state (disabled, deleted, etc.)
     * @since 1.0.0
     */
    public static function findIdentity($id);

    /**
     * Returns an ID that can uniquely identify a user identity.
     * @return string|int an ID that uniquely identifies a user identity.
     * @since 1.0.0
     */
    public function getId();

    /**
     * May the user use the client.
     * Note: In case of the "Client Credentials" Grant Type request there is no end-user to authorize the request,
     *       therefore this method will not be called for a "Client Credentials" request.
     *       If the "Client Credentials Grant User ID" is specified, that user is always allowed.
     * @param Oauth2ClientInterface $client
     * @param string $grantType
     * @return bool
     */
    public function isOauth2ClientAllowed($client, $grantType);
}
