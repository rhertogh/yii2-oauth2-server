<?php

namespace Yii2Oauth2ServerTests\functional;

use Codeception\Util\HttpCode;
use rhertogh\Yii2Oauth2Server\Oauth2Module;
use Yii2Oauth2ServerTests\_helpers\TestUserModelPasswordGrant;
use Yii2Oauth2ServerTests\ApiTester;
use Yii2Oauth2ServerTests\functional\_base\BaseSimpleGrantCest;

/**
 * @covers \rhertogh\Yii2Oauth2Server\controllers\web\server\Oauth2AccessTokenAction
 * @covers \rhertogh\Yii2Oauth2Server\controllers\web\Oauth2ServerController
 */
class PasswordGrantCest extends BaseSimpleGrantCest
{
    public function _before(ApiTester $I)
    {
        parent::_before($I);

        // Using TestUserModelPasswordGrant as definition for the Oauth2Module's identity class
        // in order to support the password grant.
        Oauth2Module::getInstance()->identityClass = TestUserModelPasswordGrant::class;
    }

    protected function grantTypeSupportsRefreshToken()
    {
        return true;
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
                'grant' => Oauth2Module::GRANT_TYPE_IDENTIFIER_PASSWORD,
                'accessTokenTTL' => 'PT10M',
                'providerOptions' => [
                    'clientId' => 'test-client-type-password-public-valid',
                ],
                'tokenOptions' => [
                    'username' => 'test.user',
                    'password' => 'password',
                ],
                'responseCode' => HttpCode::OK,
            ],

            // UNAUTHORIZED.
            [
                'grant' => Oauth2Module::GRANT_TYPE_IDENTIFIER_PASSWORD,
                'accessTokenTTL' => 'PT10M',
                'providerOptions' => [
                    'clientId' => 'test-client-type-password-public-valid',
                ],
                'tokenOptions' => [
                    'username' => 'test.user',
                    'password' => 'incorrect',
                ],
                'responseCode' => HttpCode::BAD_REQUEST,
            ],
        ];
    }
}
