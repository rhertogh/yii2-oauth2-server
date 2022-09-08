<?php

namespace Yii2Oauth2ServerTests\unit\filters\auth;

use League\OAuth2\Server\Exception\OAuthServerException;
use rhertogh\Yii2Oauth2Server\filters\auth\Oauth2HttpBearerAuth;
use Yii;
use yii\base\Module;
use yii\web\UnauthorizedHttpException;
use Yii2Oauth2ServerTests\_helpers\TestUserModel;
use Yii2Oauth2ServerTests\unit\DatabaseTestCase;

/**
 * @covers \rhertogh\Yii2Oauth2Server\filters\auth\Oauth2HttpBearerAuth
 */
class Oauth2HttpBearerAuthTest extends DatabaseTestCase
{
    public function testAuthenticate()
    {
        // phpcs:disable Generic.Files.LineLength.TooLong -- readability acually better on single line
        $this->mockWebApplication([
            'modules' => [
                'oauth2' => [
                    'resourceServerAccessTokenRevocationValidation' => false, // Token revocation validation is tested during functional testing.
                ]
            ]
        ]);
        // phpcs:enable Generic.Files.LineLength.TooLong

        $request = Yii::$app->request;
        $request->headers->set('Authorization', 'Bearer ' . $this->validAccessToken);

        $httpBearerAuth = new Oauth2HttpBearerAuth();

        $identity = $httpBearerAuth->authenticate(Yii::$app->user, $request, Yii::$app->response);
        $this->assertNotNull($identity);
        $this->assertEquals(123, $identity->getId());
    }

    public function testAuthenticateCustomModuleName()
    {
        $this->mockWebApplication([
            'modules' => [
                'customOauth2' => get_class(new class ('test') extends Module {
                    public function validateAuthenticatedRequest()
                    {
                        throw OAuthServerException::accessDenied();
                    }
                })
            ]
        ]);

        $request = Yii::$app->request;
        $request->headers->set('Authorization', 'Bearer ' . $this->validAccessToken);

        $httpBearerAuth = new Oauth2HttpBearerAuth([
            'oauth2ModuleName' => 'customOauth2'
        ]);

        $identity = $httpBearerAuth->authenticate(Yii::$app->user, $request, Yii::$app->response);
        $this->assertNull($identity);
    }

    public function testAuthenticateNoAuthHeader()
    {
        $this->mockWebApplication();

        $httpBearerAuth = new Oauth2HttpBearerAuth();

        $this->assertNull($httpBearerAuth->authenticate(Yii::$app->user, Yii::$app->request, Yii::$app->response));
    }

    public function testAuthenticateNonExistingIdentity()
    {
        // phpcs:disable Generic.Files.LineLength.TooLong -- readability acually better on single line
        $this->mockWebApplication([
            'modules' => [
                'oauth2' => [
                    'resourceServerAccessTokenRevocationValidation' => false, // Token revocation validation is tested during functional testing.
                ]
            ],
            'components' => [
                'user' => [
                    'identityClass' => get_class(new class extends TestUserModel {
                        public static function findIdentityByAccessToken($token, $type = null)
                        {
                            return null;
                        }
                    })
                ],
            ],
        ]);
        // phpcs:enable Generic.Files.LineLength.TooLong

        $request = Yii::$app->request;
        $response = Yii::$app->response;
        $request->headers->set('Authorization', 'Bearer ' . $this->validAccessToken);

        $httpBearerAuth = new Oauth2HttpBearerAuth([
            'realm' => 'test-realm',
        ]);

        try {
            $httpBearerAuth->authenticate(Yii::$app->user, $request, $response);
        } catch (\Exception $e) {
        }

        $this->assertInstanceOf(UnauthorizedHttpException::class, $e);
        $this->assertEquals('Bearer realm="test-realm"', $response->headers->get('WWW-Authenticate'));
    }

    public function testAuthenticateNonBearer()
    {
        $this->mockWebApplication();
        $request = Yii::$app->request;

        $httpBearerAuth = new Oauth2HttpBearerAuth();
        $request->headers->set('Authorization', 'test ' . $this->validAccessToken);

        $this->assertNull($httpBearerAuth->authenticate(Yii::$app->user, $request, Yii::$app->response));
    }

    public function testAuthenticateInvalidToken()
    {
        $this->mockWebApplication();
        $request = Yii::$app->request;

        $httpBearerAuth = new Oauth2HttpBearerAuth();
        $request->headers->set('Authorization', 'Bearer invalid-access-token');

        $this->assertNull($httpBearerAuth->authenticate(Yii::$app->user, $request, Yii::$app->response));
    }
}
