<?php

namespace Yii2Oauth2ServerTests\functional\grants;

use Codeception\Util\HttpCode;
use rhertogh\Yii2Oauth2Server\Oauth2Module;
use Yii2Oauth2ServerTests\functional\_base\BaseSimpleGrantCest;

/**
 * @covers \rhertogh\Yii2Oauth2Server\controllers\web\server\Oauth2AccessTokenAction
 * @covers \rhertogh\Yii2Oauth2Server\controllers\web\Oauth2ServerController
 */
class ClientCredentialsGrantCest extends BaseSimpleGrantCest
{
    protected function grantTypeSupportsRefreshToken()
    {
        return false;
    }

    /**
     * @return array[]
     * @see BaseSimpleGrantCest::simpleGrantTest()
     */
    protected function simpleGrantTestProvider()
    {
        return [

            // OK.
            [
                'grant' => Oauth2Module::GRANT_TYPE_IDENTIFIER_CLIENT_CREDENTIALS,
                'accessTokenTTL' => 'PT10M',
                'providerOptions' => [
                    'clientId' => 'test-client-type-client-credentials-valid',
                    'clientSecret' => 'secret',
                ],
                'tokenOptions' => [],
                'responseCode' => HttpCode::OK
            ],

            // UNAUTHORIZED.
            [
                'grant' => Oauth2Module::GRANT_TYPE_IDENTIFIER_CLIENT_CREDENTIALS,
                'accessTokenTTL' => 'PT10M',
                'providerOptions' => [
                    'clientId' => 'test-client-type-client-credentials-valid',
                    'clientSecret' => 'incorrect',
                ],
                'tokenOptions' => [],
                'responseCode' => HttpCode::UNAUTHORIZED
            ],
        ];
    }
}
