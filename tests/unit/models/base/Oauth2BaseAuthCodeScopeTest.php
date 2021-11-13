<?php

namespace Yii2Oauth2ServerTests\unit\models\base;

use rhertogh\Yii2Oauth2Server\interfaces\models\queries\Oauth2AuthCodeQueryInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\queries\Oauth2AuthCodeScopeQueryInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\queries\Oauth2ScopeQueryInterface;
use rhertogh\Yii2Oauth2Server\models\base\Oauth2AuthCodeScope;
use Yii2Oauth2ServerTests\unit\models\base\_base\BaseOauth2BaseModelsTest;

/**
 * @covers \rhertogh\Yii2Oauth2Server\models\base\Oauth2AuthCodeScope
 */
class Oauth2BaseAuthCodeScopeTest extends BaseOauth2BaseModelsTest
{
    /**
     * @inheritDoc
     */
    public function getBaseModelClass()
    {
        return Oauth2AuthCodeScope::class;
    }

    /**
     * @inheritDoc
     */
    protected function getQueryClass()
    {
        return Oauth2AuthCodeScopeQueryInterface::class;
    }

    /**
     * @inheritDoc
     * @see BaseOauth2BaseModelsTest::testAttributeLabels()
     */
    public function attributeLabelsProvider()
    {
        // Note: when changing these, also update translation files
        return [[[
            'auth_code_id' => 'Auth Code ID',
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
            ['authCode', Oauth2AuthCodeQueryInterface::class, false],
            ['scope',    Oauth2ScopeQueryInterface::class,    false],
        ];
    }
}
