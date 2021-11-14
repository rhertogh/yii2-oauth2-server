<?php

namespace Yii2Oauth2ServerTests\unit\models\base;

use rhertogh\Yii2Oauth2Server\interfaces\models\queries\Oauth2ScopeQueryInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\queries\Oauth2UserClientQueryInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\queries\Oauth2UserClientScopeQueryInterface;
use rhertogh\Yii2Oauth2Server\models\base\Oauth2UserClientScope;
use Yii2Oauth2ServerTests\unit\models\base\_base\BaseOauth2BaseModelsTest;

/**
 * @covers \rhertogh\Yii2Oauth2Server\models\base\Oauth2UserClientScope
 */
class Oauth2BaseUserClientScopeTest extends BaseOauth2BaseModelsTest
{
    /**
     * @inheritDoc
     */
    public function getBaseModelClass()
    {
        return Oauth2UserClientScope::class;
    }

    /**
     * @inheritDoc
     */
    protected function getQueryClass()
    {
        return Oauth2UserClientScopeQueryInterface::class;
    }

    /**
     * @inheritDoc
     * @see BaseOauth2BaseModelsTest::testAttributeLabels()
     */
    public function attributeLabelsProvider()
    {
        // Note: when changing these, also update translation files.
        return [[[
            'user_id' => 'User ID',
            'client_id' => 'Client ID',
            'scope_id' => 'Scope ID',
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
            ['scope',      Oauth2ScopeQueryInterface::class,      false],
            ['userClient', Oauth2UserClientQueryInterface::class, false],
        ];
    }
}
