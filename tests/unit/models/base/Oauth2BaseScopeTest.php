<?php
namespace Yii2Oauth2ServerTests\unit\models\base;

use rhertogh\Yii2Oauth2Server\interfaces\models\queries\Oauth2AccessTokenQueryInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\queries\Oauth2AccessTokenScopeQueryInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\queries\Oauth2AuthCodeQueryInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\queries\Oauth2AuthCodeScopeQueryInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\queries\Oauth2ClientQueryInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\queries\Oauth2ClientScopeQueryInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\queries\Oauth2ScopeQueryInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\queries\Oauth2UserClientQueryInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\queries\Oauth2UserClientScopeQueryInterface;
use rhertogh\Yii2Oauth2Server\models\base\Oauth2Scope;
use Yii2Oauth2ServerTests\unit\models\base\_base\BaseOauth2BaseModelsTest;

/**
 * @covers \rhertogh\Yii2Oauth2Server\models\base\Oauth2Scope
 */
class Oauth2BaseScopeTest extends BaseOauth2BaseModelsTest
{
    /**
     * @inheritDoc
     */
    public function getBaseModelClass()
    {
        return Oauth2Scope::class;
    }

    /**
     * @inheritDoc
     */
    protected function getQueryClass()
    {
        return Oauth2ScopeQueryInterface::class;
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
            'description' => 'Description',
            'applied_by_default' => 'Applied By Default',
            'required_on_authorization' => 'Required On Authorization',
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
            ['accessTokens',      Oauth2AccessTokenQueryInterface::class,      true],
            ['accessTokenScopes', Oauth2AccessTokenScopeQueryInterface::class, true],
            ['authCodes',         Oauth2AuthCodeQueryInterface::class,         true],
            ['authCodeScopes',    Oauth2AuthCodeScopeQueryInterface::class,    true],
            ['clientScopes',      Oauth2ClientScopeQueryInterface::class,      true],
            ['clients',           Oauth2ClientQueryInterface::class,           true],
            ['userClientScopes',  Oauth2UserClientScopeQueryInterface::class,  true],
            ['userClients',       Oauth2UserClientQueryInterface::class,  true],
        ];
    }
}
