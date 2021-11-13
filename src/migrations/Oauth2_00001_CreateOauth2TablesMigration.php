<?php

namespace rhertogh\Yii2Oauth2Server\migrations;

use rhertogh\Yii2Oauth2Server\interfaces\models\Oauth2AccessTokenInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\Oauth2AccessTokenScopeInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\Oauth2AuthCodeInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\Oauth2AuthCodeScopeInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\Oauth2ClientInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\Oauth2ClientScopeInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\Oauth2RefreshTokenInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\Oauth2ScopeInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\Oauth2UserClientInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\Oauth2UserClientScopeInterface;
use rhertogh\Yii2Oauth2Server\migrations\base\Oauth2BaseMigration;
use rhertogh\Yii2Oauth2Server\models\Oauth2AccessToken;
use rhertogh\Yii2Oauth2Server\models\Oauth2Client;
use rhertogh\Yii2Oauth2Server\models\Oauth2Scope;
use rhertogh\Yii2Oauth2Server\Oauth2Module;
use yii\base\InvalidConfigException;
use yii\db\Schema;

/**
 * phpcs:disable Squiz.Classes.ValidClassName.NotCamelCaps
 * phpcs:disable Generic.Files.LineLength.TooLong
 */
abstract class Oauth2_00001_CreateOauth2TablesMigration extends Oauth2BaseMigration
{
    /**
     * @var int Number of tables expected to be returned by getTables(),
     * when dependency injection is misconfigured this can be off.
     */
    protected $numTables = 10;

    /**
     * @inheritDoc
     */
    public static function generationIsActive($module)
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function safeUp()
    {
        foreach ($this->getTables() as $table => $definition) {
            $this->createTable($table, $definition);
        }
    }

    /**
     * @inheritDoc
     */
    public function safeDown()
    {
        foreach (array_reverse($this->getTables()) as $table => $definition) {
            $this->dropTable($table);
        }
    }

    /**
     * Get all table definitions.
     * @return array[]
     * @throws InvalidConfigException
     * @since 1.0.0
     */
    protected function getTables()
    {
        $module = Oauth2Module::getInstance();
        if (empty($module)) {
            throw new InvalidConfigException('Oauth2Module is not instantiated. Is it added to the config in the "module" and "bootstrap" section?');
        }

        $accessTokenTable = $this->getTableName(Oauth2AccessTokenInterface::class);
        $accessTokenScopeTable = $this->getTableName(Oauth2AccessTokenScopeInterface::class);
        $authCodeTable = $this->getTableName(Oauth2AuthCodeInterface::class);
        $authCodeScopeTable = $this->getTableName(Oauth2AuthCodeScopeInterface::class);
        $clientTable = $this->getTableName(Oauth2ClientInterface::class);
        $clientScopeTable = $this->getTableName(Oauth2ClientScopeInterface::class);
        $refreshTokenTable = $this->getTableName(Oauth2RefreshTokenInterface::class);
        $scopeTable = $this->getTableName(Oauth2ScopeInterface::class);
        $userClientTable = $this->getTableName(Oauth2UserClientInterface::class);
        $userClientScopeTable = $this->getTableName(Oauth2UserClientScopeInterface::class);

        $userTableSchema = $this->getTableSchema($module->identityClass);
        if ($userTableSchema) {
            if (count($userTableSchema->primaryKey) != 1) {
                throw new InvalidConfigException('The primary key of `userClass` must consist of a single column');
            }
            $userTable = $userTableSchema->name;
            $userPkColumn = $userTableSchema->primaryKey[0];
            $userPkSchema = $userTableSchema->columns[$userPkColumn];
        } else {
            $userTable = false;
            $userPkColumn = null;
            $userPkSchema = null;
        }

        // See https://datatracker.ietf.org/doc/html/rfc7591#section-2 (although not yet fully implemented, some fields follow this standard)
        $tables = [
            $clientTable => [
                'id' => $this->primaryKey(),
                'identifier' => $this->string()->notNull()->unique(),
                'type' => $this->boolean()->notNull()->defaultValue(Oauth2ClientInterface::TYPE_CONFIDENTIAL),
                'secret' => $this->text(),
                'name' => $this->string()->notNull(),
                'logo_uri' => $this->string(),
                'tos_uri' => $this->string(),
                'contacts' => $this->text()
                    ->comment('JSON encoded array of strings with contact details for the client.'),
                'redirect_uris' => $this->json(),
                'token_types' => $this->integer()->notNull()->defaultValue(Oauth2AccessToken::TYPE_BEARER),
                'grant_types' => $this->integer()->notNull()->defaultValue(Oauth2Module::GRANT_TYPE_AUTH_CODE | Oauth2Module::GRANT_TYPE_REFRESH_TOKEN),
                'scope_access' => $this->integer()->notNull()->defaultValue(Oauth2Client::SCOPE_ACCESS_STRICT)
                    ->comment('Determines how strict scopes must be defined for this client.'),
                'user_account_selection' => $this->integer()->comment('Determines when to show user account selection screen. Using Oauth2Module::$defaultUserAccountSelection when `null`.'),
                'allow_auth_code_without_pkce' => $this->boolean()->notNull()->defaultValue(0)
                    ->comment('Require clients to use PKCE when using the auth_code grant type.'),
                'skip_authorization_if_scope_is_allowed' => $this->boolean()->notNull()->defaultValue(0)
                    ->comment('Skip user authorization of client if there are no scopes that require authorization.'),
                'client_credentials_grant_user_id' => ($userTable ? $userPkSchema->dbType : Schema::TYPE_STRING)
                    . ' COMMENT "Optional user id to use in case of grant type \'client_credentials\'.'
                    . ' This user account should also be connected to the client via the `' . $userClientTable . '` table and, if applicable, the `' . $userClientScopeTable . '` table."',
                'oidc_allow_offline_access_without_consent' => $this->boolean()->notNull()->defaultValue(0)
                    ->comment('Allow the OpenID Connect "offline_access" scope for this client without the "prompt" parameter contains "consent".'),
                'oidc_userinfo_encrypted_response_alg' => $this->string(),
                'enabled' => $this->boolean()->notNull()->defaultValue(1),
                'created_at' => $this->integer()->notNull(),
                'updated_at' => $this->integer()->notNull(),

                ...($userTable
                    ? ['FOREIGN KEY (client_credentials_grant_user_id) REFERENCES ' . $userTable . ' (' . $userPkColumn . ') ON DELETE RESTRICT ON UPDATE CASCADE']
                    : ['KEY (client_credentials_grant_user_id)']
                ),
                'KEY (token_types)',
                'KEY (grant_types)',
                'KEY (enabled)',
            ],

            $scopeTable => [
                'id' => $this->primaryKey(),
                'identifier' => $this->string()->notNull()->unique(),
                'description' => $this->text(),
                'authorization_message' => $this->text(),
                'applied_by_default' => $this->string()->notNull()->defaultValue(Oauth2Scope::APPLIED_BY_DEFAULT_NO),
                'required_on_authorization' => $this->boolean()->notNull()->defaultValue(1),
                'enabled' => $this->boolean()->notNull()->defaultValue(1),
                'created_at' => $this->integer()->notNull(),
                'updated_at' => $this->integer()->notNull(),

                'KEY (applied_by_default)',
                'KEY (enabled)',
            ],

            $clientScopeTable => [
                //'id' => $this->primaryKey(),
                'client_id' => $this->integer()->notNull(),
                'scope_id' => $this->integer()->notNull(),
                'applied_by_default' => $this->integer(),
                'required_on_authorization' => $this->boolean(),
                'enabled' => $this->boolean()->notNull()->defaultValue(1),
                'created_at' => $this->integer()->notNull(),
                'updated_at' => $this->integer()->notNull(),
                'PRIMARY KEY (client_id, scope_id)',
                'FOREIGN KEY (client_id) REFERENCES ' . $clientTable . ' (id) ON DELETE CASCADE ON UPDATE CASCADE',
                'FOREIGN KEY (scope_id) REFERENCES ' . $scopeTable . ' (id) ON DELETE CASCADE ON UPDATE CASCADE',
                'KEY (enabled)',
                'KEY (applied_by_default)',
            ],

            $authCodeTable => [
                'id' => $this->bigPrimaryKey()->unsigned(),
                'identifier' => $this->string()->notNull()->unique(),
                'redirect_uri' => $this->string(),
                'expiry_date_time' => $this->dateTime()->notNull(),
                'client_id' => $this->integer()->notNull(),
                'user_id' => ($userTable ? $userPkSchema->dbType : Schema::TYPE_STRING) . ' NOT NULL',
                'enabled' => $this->boolean()->notNull()->defaultValue(1), // ToDo: do we need this ???
                'created_at' => $this->integer()->notNull(),
                'updated_at' => $this->integer()->notNull(),

                'FOREIGN KEY (client_id) REFERENCES ' . $clientTable . ' (id) ON DELETE CASCADE ON UPDATE CASCADE',
                ...($userTable
                    ? ['FOREIGN KEY (user_id) REFERENCES ' . $userTable . ' (' . $userPkColumn . ') ON DELETE CASCADE ON UPDATE CASCADE']
                    : ['KEY (user_id)']
                ),
                'KEY (enabled)',
            ],

            $authCodeScopeTable => [
                'auth_code_id' => $this->bigInteger()->unsigned()->notNull(),
                'scope_id' => $this->integer()->notNull(),
                'created_at' => $this->integer()->notNull(),

                'PRIMARY KEY (auth_code_id, scope_id)',
                'FOREIGN KEY (auth_code_id) REFERENCES ' . $authCodeTable . ' (id) ON DELETE CASCADE ON UPDATE CASCADE',
                'FOREIGN KEY (scope_id) REFERENCES ' . $scopeTable . ' (id) ON DELETE CASCADE ON UPDATE CASCADE',
            ],

            $accessTokenTable => [
                'id' => $this->bigPrimaryKey()->unsigned(),
                'identifier' => $this->string()->notNull()->unique(),
                'client_id' => $this->integer()->notNull(),
                'user_id' => ($userTable ? $userPkSchema->dbType : Schema::TYPE_STRING) . ' DEFAULT NULL',
                'type' => $this->integer()->notNull(),
                'mac_key' => $this->string(500),
                'mac_algorithm' => Schema::TYPE_SMALLINT,
                'allowance' => Schema::TYPE_SMALLINT,
                'allowance_updated_at' => $this->integer(),
                'expiry_date_time' => $this->dateTime()->notNull(),
                'enabled' => $this->boolean()->notNull()->defaultValue(1),
                'created_at' => $this->integer()->notNull(),
                'updated_at' => $this->integer()->notNull(),

                'FOREIGN KEY (client_id) REFERENCES ' . $clientTable . ' (id) ON DELETE CASCADE ON UPDATE CASCADE',
                ...($userTable
                    ? ['FOREIGN KEY (user_id) REFERENCES ' . $userTable . ' (' . $userPkColumn . ') ON DELETE CASCADE ON UPDATE CASCADE']
                    : ['KEY (user_id)']
                ),
                'KEY (type)',
                'KEY (mac_algorithm)',
                'KEY (enabled)',
            ],

            $accessTokenScopeTable => [
                'access_token_id' => $this->bigInteger()->unsigned()->notNull(),
                'scope_id' => $this->integer()->notNull(),
                'created_at' => $this->integer()->notNull(),

                'PRIMARY KEY (access_token_id, scope_id)',
                'FOREIGN KEY (access_token_id) REFERENCES ' . $accessTokenTable . ' (id) ON DELETE CASCADE ON UPDATE CASCADE',
                'FOREIGN KEY (scope_id) REFERENCES ' . $scopeTable . ' (id) ON DELETE CASCADE ON UPDATE CASCADE',
            ],

            $refreshTokenTable => [
                'id' => $this->bigPrimaryKey()->unsigned(),
                'access_token_id' => $this->bigInteger()->unsigned(),
                'identifier' => $this->string()->notNull()->unique(),
                'expiry_date_time' => $this->dateTime()->notNull(),
                'enabled' => $this->boolean()->notNull()->defaultValue(1),
                'created_at' => $this->integer()->notNull(),
                'updated_at' => $this->integer()->notNull(),

                'FOREIGN KEY (access_token_id) REFERENCES ' . $accessTokenTable . ' (id) ON DELETE CASCADE ON UPDATE CASCADE',
                'KEY (enabled)',
            ],

            $userClientTable => [
                'user_id' => ($userTable ? $userPkSchema->dbType : Schema::TYPE_STRING) . ' NOT NULL',
                'client_id' => $this->integer()->notNull(),
                'enabled' => $this->boolean()->notNull()->defaultValue(1),
                'created_at' => $this->integer()->notNull(),
                'updated_at' => $this->integer()->notNull(),

                'PRIMARY KEY (user_id, client_id)',
                ...($userTable
                    ? ['FOREIGN KEY (user_id) REFERENCES ' . $userTable . ' (' . $userPkColumn . ') ON DELETE CASCADE ON UPDATE CASCADE']
                    : ['KEY (user_id)']
                ),
                'FOREIGN KEY (client_id) REFERENCES ' . $clientTable . ' (id) ON DELETE CASCADE ON UPDATE CASCADE',
                'KEY (enabled)',
            ],

            $userClientScopeTable => [
                'user_id' => ($userTable ? $userPkSchema->dbType : Schema::TYPE_STRING) . ' NOT NULL',
                'client_id' => $this->integer()->notNull(),
                'scope_id' => $this->integer()->notNull(),
                'enabled' => $this->boolean()->notNull()->defaultValue(1),
                'created_at' => $this->integer()->notNull(),
                'updated_at' => $this->integer()->notNull(),

                'PRIMARY KEY (user_id, client_id, scope_id)',
                'FOREIGN KEY (user_id, client_id) REFERENCES ' . $userClientTable . ' (user_id, client_id) ON DELETE CASCADE ON UPDATE CASCADE',
                'FOREIGN KEY (scope_id) REFERENCES ' . $scopeTable . ' (id) ON DELETE CASCADE ON UPDATE CASCADE', # Note: Not connected to client_scope table since scopes can also be applied by default to all clients
                'KEY (enabled)',
            ],
        ];

        if (count(array_unique(array_keys($tables))) != $this->numTables) {
            throw new InvalidConfigException('Incorrect number of tables in definition. Are the Active Record classes correctly configured?');
        }

        return $tables;
    }

//    /**
//     * @param string $tableClass
//     * @return ActiveRecord
//     * @throws InvalidConfigException
//     */
//    protected function getArInstance($tableClass)
//    {
//        $activeRecord = Yii::createObject($tableClass);
//
//        if (!($activeRecord instanceof ActiveRecord)) {
//            throw new InvalidConfigException($tableClass . ' must be an instance of ActiveRecord');
//        }
//
//        return $activeRecord;
//    }
}
