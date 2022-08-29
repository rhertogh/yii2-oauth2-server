<?php

namespace Yii2Oauth2ServerTests\functional\_base;

use Codeception\Util\HttpCode;
use League\OAuth2\Client\Token\AccessToken;
use Yii;
use yii\authclient\OAuthToken;
use yii\di\Container;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use Yii2Oauth2ServerTests\_helpers\ClientTokenProvider;
use Yii2Oauth2ServerTests\_helpers\fixtures\FullDbFixture;
use Yii2Oauth2ServerTests\ApiTester;

abstract class BaseGrantCest
{
    /**
     * @var string the driver name of this test class. Must be set by a subclass.
     */
    protected $driverName = 'mysql';

    /**
     * @throws \yii\db\Exception
     */
    public function _before(ApiTester $I)
    {
        // Nothing to do here at the moment
    }

    /**
     * @throws \yii\db\Exception
     */
    public function _fixtures()
    {
        return [
            'db' => FullDbFixture::class,
        ];
    }

    /**
     * Clean up after test.
     * By default, the application created with [[mockApplication]] will be destroyed.
     */
    protected function _after()
    {
        $logger = Yii::getLogger();
        $logger->flush();

        Yii::$app = null;
        Yii::$container = new Container();
    }

    /**
     * @param array $options
     * @return ClientTokenProvider
     */
    protected function getProvider($options)
    {
        return new ClientTokenProvider(ArrayHelper::merge(
            [
                'clientId'                => null, # Must be set via $options
                'clientSecret'            => null, # Must be set via $options
                'redirectUri'             => null,
                'urlAuthorize'            => '/oauth2/authorize',
                'urlAccessToken'          => '/oauth2/access-token',
                'urlResourceOwnerDetails' => null,
                'scopeSeparator'          => ' ',
            ],
            $options,
        ));
    }

    /**
     * @param ApiTester $I
     * @param AccessToken|OAuthToken $authToken
     * @param $expectedProperties
     */
    protected function testAuthenticatedRequest(ApiTester $I, $authToken, $expectedProperties)
    {
        $propertyDefinitions = [
            'id' => ['enum' => [123]],
            'username' => ['enum' => ['test.user']],
            'email_address' => ['enum' => ['test.user@test.test']],
            'enabled' => ['enum' => [1]],
            'created_at' => ['type' => 'integer', 'minimum' => 1609455600],
            'updated_at' => ['type' => 'integer', 'minimum' => 1609455600],
        ];

        // ensure only requested scopes are included.
        $properties = array_intersect_key($propertyDefinitions, array_flip($expectedProperties))
            + array_fill_keys(array_diff(array_keys($propertyDefinitions), $expectedProperties), ['not' => []]);

        $I->amBearerAuthenticated($authToken->getToken());
        $I->send('GET', 'http://localhost/test/api/me');
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();
        $I->seeResponseIsValidOnJsonSchemaString(Json::encode([
            'type' => 'object',
            'required' => $expectedProperties,
            'properties' => $properties,
        ]));
    }

    /**
     * @param ApiTester $I
     */
    protected function validateAccessTokenResponse($I)
    {
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();
        $I->seeResponseIsValidOnJsonSchemaString(Json::encode([
            'type' => 'object',
            'required' => [
                'token_type',
                'expires_in',
                'access_token',
                'refresh_token',
            ],
            'properties' => [
                'token_type' => ['type' => 'string', 'pattern' => 'Bearer'],
                'expires_in' => ['type' => 'integer', 'minimum' => 1],
                'access_token' => ['type' => 'string', 'minLength' => 100],
                'refresh_token' => ['type' => 'string', 'minLength' => 100],
            ],
        ]));
    }
}
