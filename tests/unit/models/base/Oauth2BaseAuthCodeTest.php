<?php
namespace Yii2Oauth2ServerTests\unit\models\base;

use rhertogh\Yii2Oauth2Server\interfaces\models\queries\Oauth2AuthCodeQueryInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\queries\Oauth2ClientQueryInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\queries\Oauth2ScopeQueryInterface;
use rhertogh\Yii2Oauth2Server\models\base\Oauth2AuthCode;
use Yii2Oauth2ServerTests\unit\models\base\_base\BaseOauth2BaseModelsTest;

/**
 * @covers \rhertogh\Yii2Oauth2Server\models\base\Oauth2AuthCode
 */
class Oauth2BaseAuthCodeTest extends BaseOauth2BaseModelsTest
{
    /**
     * @inheritDoc
     */
    public function getBaseModelClass()
    {
        return Oauth2AuthCode::class;
    }

    /**
     * @inheritDoc
     */
    protected function getQueryClass()
    {
        return Oauth2AuthCodeQueryInterface::class;
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
            'redirect_uri' => 'Redirect Uri',
            'expiry_date_time' => 'Expiry Date Time',
            'client_id' => 'Client ID',
            'user_id' => 'User ID',
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
            ['scopes', Oauth2ScopeQueryInterface::class,  true],
        ];
    }
}
