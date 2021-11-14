<?php

namespace Yii2Oauth2ServerTests\unit\models\base;

use rhertogh\Yii2Oauth2Server\interfaces\models\queries\Oauth2AccessTokenQueryInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\queries\Oauth2AccessTokenScopeQueryInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\queries\Oauth2ScopeQueryInterface;
use rhertogh\Yii2Oauth2Server\models\base\Oauth2AccessTokenScope;
use Yii2Oauth2ServerTests\unit\models\base\_base\BaseOauth2BaseModelsTest;

/**
 * @covers \rhertogh\Yii2Oauth2Server\models\base\Oauth2AccessTokenScope
 */
class Oauth2BaseAccessTokenScopeTest extends BaseOauth2BaseModelsTest
{
    /**
     * @inheritDoc
     */
    public function getBaseModelClass()
    {
        return Oauth2AccessTokenScope::class;
    }

    /**
     * @inheritDoc
     */
    protected function getQueryClass()
    {
        return Oauth2AccessTokenScopeQueryInterface::class;
    }

    /**
     * @inheritDoc
     * @see BaseOauth2BaseModelsTest::testAttributeLabels()
     */
    public function attributeLabelsProvider()
    {
        // Note: when changing these, also update translation files.
        return [[[
            'access_token_id' => 'Access Token ID',
            'scope_id' => 'Scope ID',
            'created_at' => 'Created At',
        ]]];
    }

    /**
     * @inheritDoc
     * @see BaseOauth2BaseModelsTest::testRelations()
     */
    public function relationsProvider()
    {
        return [
            ['accessToken', Oauth2AccessTokenQueryInterface::class, false],
            ['scope',       Oauth2ScopeQueryInterface::class,       false],
        ];
    }
}
