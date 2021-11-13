<?php

namespace rhertogh\Yii2Oauth2Server\migrations;

use rhertogh\Yii2Oauth2Server\components\openidconnect\scopes\Oauth2OidcScope;
use rhertogh\Yii2Oauth2Server\interfaces\components\openidconnect\scope\Oauth2OidcScopeCollectionInterface;
use rhertogh\Yii2Oauth2Server\interfaces\components\openidconnect\scope\Oauth2OidcScopeInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\Oauth2ClientInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\Oauth2ScopeInterface;
use rhertogh\Yii2Oauth2Server\migrations\base\Oauth2BaseMigration;
use yii\helpers\ArrayHelper;

abstract class Oauth2_00002_InsertOpenIdConnectScopesMigration extends Oauth2BaseMigration
{
    /**
     * @inheritDoc
     */
    public static function generationIsActive($module)
    {
        return $module->enableOpenIdConnect;
    }

    /**
     * Default value for Scope's `required_on_authorization`.
     * @var bool
     * @since 1.0.0
     */
    public $scopeRequiredOnAuthorization = true;

    /**
     * Default description for OpenID Connect scopes
     * @var array[]
     */
    protected $openIdConnectScopes = [
        [
            'identifier' => Oauth2OidcScopeInterface::OPENID_CONNECT_SCOPE_OPENID,
            'description' => 'OpenID Connect scope',
        ],
        [
            'identifier' => Oauth2OidcScopeInterface::OPENID_CONNECT_SCOPE_PROFILE,
            'description' => 'OpenID Connect profile scope',
        ],
        [
            'identifier' => Oauth2OidcScopeInterface::OPENID_CONNECT_SCOPE_EMAIL,
            'description' => 'OpenID Connect email scope',
        ],
        [
            'identifier' => Oauth2OidcScopeInterface::OPENID_CONNECT_SCOPE_ADDRESS,
            'description' => 'OpenID Connect address scope',
        ],
        [
            'identifier' => Oauth2OidcScopeInterface::OPENID_CONNECT_SCOPE_PHONE,
            'description' => 'OpenID Connect phone scope',
        ],
        [
            'identifier' => Oauth2OidcScopeInterface::OPENID_CONNECT_SCOPE_OFFLINE_ACCESS,
            'description' => 'OpenID Offline Access scope',
        ],
    ];

    /**
     * @inheritDoc
     */
    public function safeUp()
    {
        $tableName = $this->getTableName(Oauth2ScopeInterface::class);
        foreach ($this->openIdConnectScopes as $scope) {
            $this->insert($tableName, ArrayHelper::merge([
                'required_on_authorization' => (int)$this->scopeRequiredOnAuthorization,
                'created_at' => time(),
                'updated_at' => time(),
            ], $scope));
        }
    }

    /**
     * @inheritDoc
     */
    public function safeDown()
    {
        $tableName = $this->getTableName(Oauth2ScopeInterface::class);
        $openIdConnectScopeIdentifiers = array_column($this->openIdConnectScopes, 'identifier');
        $this->delete($tableName, ['identifier' => $openIdConnectScopeIdentifiers]);
    }
}
