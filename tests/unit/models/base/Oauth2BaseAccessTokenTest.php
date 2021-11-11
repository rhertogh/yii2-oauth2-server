<?php
namespace Yii2Oauth2ServerTests\unit\models\base;

use rhertogh\Yii2Oauth2Server\interfaces\models\queries\Oauth2AccessTokenQueryInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\queries\Oauth2AccessTokenScopeQueryInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\queries\Oauth2ClientQueryInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\queries\Oauth2RefreshTokenQueryInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\queries\Oauth2ScopeQueryInterface;
use rhertogh\Yii2Oauth2Server\models\base\Oauth2AccessToken;
use Yii2Oauth2ServerTests\unit\models\base\_base\BaseOauth2BaseModelsTest;

/**
 * @covers \rhertogh\Yii2Oauth2Server\models\base\Oauth2AccessToken
 */
class Oauth2BaseAccessTokenTest extends BaseOauth2BaseModelsTest
{
    /**
     * @inheritDoc
     */
    public function getBaseModelClass()
    {
        return Oauth2AccessToken::class;
    }

    /**
     * @inheritDoc
     */
    protected function getQueryClass()
    {
        return Oauth2AccessTokenQueryInterface::class;
    }

    /**
     * @inheritDoc
     * @see BaseOauth2BaseModelsTest::testAttributeLabels()
     */
    public function attributeLabelsProvider()
    {
        // Note: when changing these, also update translation files
        return [[[
            'id' => 'ID',
            'identifier' => 'Identifier',
            'client_id' => 'Client ID',
            'user_id' => 'User ID',
            'type' => 'Type',
            'mac_key' => 'Mac Key',
            'mac_algorithm' => 'Mac Algorithm',
            'allowance' => 'Allowance',
            'allowance_updated_at' => 'Allowance Updated At',
            'expiry_date_time' => 'Expiry Date Time',
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
            ['client',            Oauth2ClientQueryInterface::class,           false],
            ['accessTokenScopes', Oauth2AccessTokenScopeQueryInterface::class, true],
            ['refreshTokens',     Oauth2RefreshTokenQueryInterface::class,     true],
            ['scopes',            Oauth2ScopeQueryInterface::class,            true],
        ];
    }
}

