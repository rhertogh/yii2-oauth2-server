<?php
namespace Yii2Oauth2ServerTests\unit\models\base;

use rhertogh\Yii2Oauth2Server\interfaces\models\queries\Oauth2AccessTokenQueryInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\queries\Oauth2RefreshTokenQueryInterface;
use rhertogh\Yii2Oauth2Server\models\base\Oauth2RefreshToken;
use Yii2Oauth2ServerTests\unit\models\base\_base\BaseOauth2BaseModelsTest;

/**
 * @covers \rhertogh\Yii2Oauth2Server\models\base\Oauth2RefreshToken
 */
class Oauth2BaseRefreshTokenTest extends BaseOauth2BaseModelsTest
{
    /**
     * @inheritDoc
     */
    public function getBaseModelClass()
    {
        return Oauth2RefreshToken::class;
    }

    /**
     * @inheritDoc
     */
    protected function getQueryClass()
    {
        return Oauth2RefreshTokenQueryInterface::class;
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
            'access_token_id' => 'Access Token ID',
            'identifier' => 'Identifier',
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
            ['accessToken', Oauth2AccessTokenQueryInterface::class, false],
        ];
    }
}
