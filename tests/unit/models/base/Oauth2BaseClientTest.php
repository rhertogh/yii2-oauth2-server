<?php

namespace Yii2Oauth2ServerTests\unit\models\base;

use rhertogh\Yii2Oauth2Server\interfaces\models\queries\Oauth2AccessTokenQueryInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\queries\Oauth2AuthCodeQueryInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\queries\Oauth2ClientQueryInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\queries\Oauth2ClientScopeQueryInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\queries\Oauth2ScopeQueryInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\queries\Oauth2UserClientQueryInterface;
use rhertogh\Yii2Oauth2Server\models\base\Oauth2Client;
use Yii2Oauth2ServerTests\unit\models\base\_base\BaseOauth2BaseModelsTest;

/**
 * @covers \rhertogh\Yii2Oauth2Server\models\base\Oauth2Client
 */
class Oauth2BaseClientTest extends BaseOauth2BaseModelsTest
{
    /**
     * @inheritDoc
     */
    public function getBaseModelClass()
    {
        return Oauth2Client::class;
    }

    /**
     * @inheritDoc
     */
    protected function getQueryClass()
    {
        return Oauth2ClientQueryInterface::class;
    }

    /**
     * @inheritDoc
     * @see BaseOauth2BaseModelsTest::testAttributeLabels()
     */
    public function attributeLabelsProvider()
    {
        // Note: when changing these, also update translation files.
        return [[[
            'id' => 'ID',
            'identifier' => 'Identifier',
            'name' => 'Name',
            'type' => 'Type',
            'secret' => 'Secret',
            'old_secret' => 'Old Secret',
            'old_secret_valid_until' => 'Old Secret Valid Until',
            'logo_uri' => 'Logo Uri',
            'tos_uri' => 'Tos Uri',
            'contacts' => 'Contacts',
            'redirect_uris' => 'Redirect Uris',
            'allow_variable_redirect_uri_query' => 'Allow Variable Redirect Uri Query',
            'token_types' => 'Token Types',
            'grant_types' => 'Grant Types',
            'scope_access' => 'Scope Access',
            'end_users_may_authorize_client' => 'End Users May Authorize Client',
            'user_account_selection' => 'User Account Selection',
            'allow_auth_code_without_pkce' => 'Allow Auth Code Without Pkce',
            'skip_authorization_if_scope_is_allowed' => 'Skip Authorization If Scope Is Allowed',
            'client_credentials_grant_user_id' => 'Client Credentials Grant User ID',
            'oidc_allow_offline_access_without_consent' => 'Oidc Allow Offline Access Without Consent',
            'oidc_userinfo_encrypted_response_alg' => 'Oidc Userinfo Encrypted Response Alg',
            'enabled' => 'Enabled',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ]]];
    }

    /**
     * @inheritDoc
     * @see BaseOauth2BaseModelsTest::testRelations()
     */
    public function relationsProvider()
    {
        return [
            ['accessTokens', Oauth2AccessTokenQueryInterface::class, true],
            ['authCodes',    Oauth2AuthCodeQueryInterface::class,    true],
            ['clientScopes', Oauth2ClientScopeQueryInterface::class, true],
            ['scopes',       Oauth2ScopeQueryInterface::class,       true],
            ['userClients',  Oauth2UserClientQueryInterface::class,  true],
        ];
    }
}
