<?php

namespace Yii2Oauth2ServerTests\unit\models\base;

use rhertogh\Yii2Oauth2Server\interfaces\models\queries\Oauth2ClientQueryInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\queries\Oauth2ClientScopeQueryInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\queries\Oauth2ScopeQueryInterface;
use rhertogh\Yii2Oauth2Server\models\base\Oauth2ClientScope;
use Yii2Oauth2ServerTests\unit\models\base\_base\BaseOauth2BaseModelsTest;

/**
 * @covers \rhertogh\Yii2Oauth2Server\models\base\Oauth2ClientScope
 */
class Oauth2BaseClientScopeTest extends BaseOauth2BaseModelsTest
{
    /**
     * @inheritDoc
     */
    public function getBaseModelClass()
    {
        return Oauth2ClientScope::class;
    }

    /**
     * @inheritDoc
     */
    protected function getQueryClass()
    {
        return Oauth2ClientScopeQueryInterface::class;
    }

    /**
     * @inheritDoc
     * @see BaseOauth2BaseModelsTest::testAttributeLabels()
     */
    public function attributeLabelsProvider()
    {
        // Note: when changing these, also update translation files.
        return [[[
            'client_id' => 'Client ID',
            'scope_id' => 'Scope ID',
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
            ['client', Oauth2ClientQueryInterface::class, false],
            ['scope',  Oauth2ScopeQueryInterface::class,  false],
        ];
    }
}
