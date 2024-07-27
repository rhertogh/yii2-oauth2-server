<?php

namespace Yii2Oauth2ServerTests\functional\OpenIdConnect\_base;

use Codeception\Example;
use Codeception\Util\HttpCode;
use rhertogh\Yii2Oauth2Server\interfaces\components\authorization\client\Oauth2ClientAuthorizationRequestInterface;
use rhertogh\Yii2Oauth2Server\Oauth2Module;
use Yii;
use yii\authclient\OpenIdConnect;
use yii\helpers\ArrayHelper;
use yii\httpclient\Client;
use Yii2Oauth2ServerTests\_helpers\ApiTesterTransport;
use Yii2Oauth2ServerTests\_helpers\OpenIdConnectTestClient;
use Yii2Oauth2ServerTests\_helpers\TestUserModelOidc;
use Yii2Oauth2ServerTests\ApiTester;
use Yii2Oauth2ServerTests\functional\_base\BaseGrantCest;

class BaseOpenIdConnectCest extends BaseGrantCest
{
    public function _before(ApiTester $I)
    {
        parent::_before($I);

        // Using TestUserModelOidc as definition for the Oauth2Module's and user component identityClass.
        Oauth2Module::getInstance()->identityClass = TestUserModelOidc::class;
        Yii::$app->user->identityClass = TestUserModelOidc::class;
    }

    protected function getOpenIdConnectTestClient($I, $config = [])
    {
        return new OpenIdConnect(ArrayHelper::merge(
            [
                'httpClient' => [
                    'class' => Client::class,
                    'transport' => [
                        'class' => ApiTesterTransport::class,
                        'apiTester' => $I,
                    ]
                ],
                'validateAuthNonce' => true,
                'returnUrl' => 'http://localhost/redirect_uri/',
                'issuerUrl' => 'https://localhost',
                'clientId' => 'test-client-type-auth-code-open-id-connect',
                'enablePkce' => true,
                'scope' => 'openid'
            ],
            $config,
        ));
    }
}
