<?php

namespace Yii2Oauth2ServerTests\functional\_base;

use Codeception\Example;
use Codeception\Util\HttpCode;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Signer\Rsa\Sha256;
use League\OAuth2\Client\Token\AccessToken;
use rhertogh\Yii2Oauth2Server\Oauth2Module;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use Yii2Oauth2ServerTests\ApiTester;

/**
 * @covers \rhertogh\Yii2Oauth2Server\controllers\web\server\Oauth2AccessTokenAction
 * @covers \rhertogh\Yii2Oauth2Server\controllers\web\Oauth2ServerController
 */
abstract class BaseSimpleGrantCest extends BaseGrantCest
{
    /**
     * @return array[]
     */
    abstract protected function simpleGrantTestProvider();

    abstract protected function grantTypeSupportsRefreshToken();

    /**
     * @dataProvider simpleGrantTestProvider
     */
    public function simpleGrantTest(ApiTester $I, Example $example)
    {
        $module = Oauth2Module::getInstance();
        $module->defaultAccessTokenTTL = $example['accessTokenTTL'];

        $provider = $this->getProvider($example['providerOptions']);
        $jwtConfiguration = Configuration::forSymmetricSigner(
            new Sha256(),
            InMemory::empty()
        );

        $expirationTime = (new \DateTimeImmutable('@' . time())) // ensure no micro seconds.
            ->add(new \DateInterval($example['accessTokenTTL']));

        $accessTokenRequest = $provider->getAccessTokenRequestWrapper($example['grant'], $example['tokenOptions']);

        $I->sendPsr7Request($accessTokenRequest);
        $I->seeResponseCodeIs($example['responseCode']);
        if ($example['responseCode'] == HttpCode::OK) {
            $I->seeResponseIsJson();
            $I->seeResponseIsValidOnJsonSchemaString(Json::encode([
                'type' => 'object',
                'required' => [
                    'token_type',
                    'expires_in',
                    'access_token',
                ],
                'properties' => [
                    'token_type' => ['type' => 'string', 'pattern' => 'Bearer'],
                    'expires_in' => ['type' => 'integer', 'minimum' => 1],
                    'access_token' => ['type' => 'string', 'minLength' => 100],
                ],
            ]));

            $response = Json::decode($I->grabResponse());
            $accessToken = new AccessToken($response);
            $I->assertFalse($accessToken->hasExpired());
            $token = $jwtConfiguration->parser()->parse($accessToken->getToken());
            $exp = $token->claims()->get('exp');
            $I->assertGreaterThanOrEqual($expirationTime, $exp);
            $I->assertLessThanOrEqual($expirationTime->modify('+1 second'), $exp);

            # region refresh access token
            if ($this->grantTypeSupportsRefreshToken()) {
                $provider = $this->getProvider($example['providerOptions']);
                $accessTokenRequest = $provider->getAccessTokenRequestWrapper('refresh_token', ArrayHelper::merge(
                    $example['tokenOptions'],
                    [
                        'refresh_token' => $accessToken->getRefreshToken(),
                    ]
                ));

                $I->sendPsr7Request($accessTokenRequest);
                $this->validateAccessTokenResponse($I);
                $response = Json::decode($I->grabResponse());
            }
            # endregion

            # region make authenticated request
            $token = new AccessToken($response);
            $this->testAuthenticatedRequest($I, $token, ['id']);
            # endregion
        }
    }
}
