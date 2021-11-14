<?php

namespace Yii2Oauth2ServerTests\unit\controllers\web\wellknown;

use rhertogh\Yii2Oauth2Server\components\openidconnect\claims\Oauth2OidcClaim;
use rhertogh\Yii2Oauth2Server\components\openidconnect\scopes\Oauth2OidcScope;
use rhertogh\Yii2Oauth2Server\controllers\web\Oauth2WellKnownController;
use rhertogh\Yii2Oauth2Server\controllers\web\wellknown\Oauth2OpenidConfigurationAction;
use rhertogh\Yii2Oauth2Server\interfaces\components\openidconnect\scope\Oauth2OidcScopeCollectionInterface;
use rhertogh\Yii2Oauth2Server\interfaces\components\openidconnect\scope\Oauth2OidcScopeInterface;
use rhertogh\Yii2Oauth2Server\Oauth2Module;
use Yii;
use Yii2Oauth2ServerTests\unit\DatabaseTestCase;

/**
 * @covers \rhertogh\Yii2Oauth2Server\controllers\web\wellknown\Oauth2OpenidConfigurationAction
 */
class Oauth2OpenidConfigurationActionTest extends DatabaseTestCase
{
    protected function getMockController()
    {
        return new Oauth2WellKnownController('wellknown', Oauth2Module::getInstance());
    }

    public function testRunOK()
    {
        $serviceDocumentationTestUrl = 'https://openIdConnectDiscoveryServiceDocumentationTestUrl';
        $this->mockWebApplication([
            'modules' => [
                'oauth2' => [
                    'openIdConnectScopes' => [
                        ...Oauth2OidcScopeCollectionInterface::OPENID_CONNECT_DEFAULT_SCOPES,
                        new Oauth2OidcScope([
                            'identifier' => 'custom-scope',
                            'claims' => [
                                new Oauth2OidcClaim([
                                    'identifier' => 'custom-claim',
                                ]),
                            ],
                        ]),
                    ],
                    'openIdConnectDiscoveryServiceDocumentationUrl' => $serviceDocumentationTestUrl,
                ],
            ],
        ]);

        Yii::$app->controller = $this->getMockController();
        $configurationAction = new Oauth2OpenidConfigurationAction('configuration', Yii::$app->controller);
        $response = $configurationAction->run();

        $expectedClaims = call_user_func_array('array_merge', [
            ...array_values(Oauth2OidcScopeInterface::OPENID_CONNECT_DEFAULT_SCOPE_CLAIMS),
            ['custom-claim']
        ]);
        sort($expectedClaims);
        $expectedClaims = array_unique($expectedClaims);

        $this->assertIsArray($response);
        $this->assertEquals('http://localhost', $response['issuer']);
        $this->assertEquals('http://localhost/oauth2/authorize', $response['authorization_endpoint']);
        $this->assertEquals('http://localhost/oauth2/access-token', $response['token_endpoint']);
        $this->assertEquals('http://localhost/oauth2/oidc/userinfo', $response['userinfo_endpoint']);
        $this->assertEquals('http://localhost/oauth2/certs', $response['jwks_uri']);
        $this->assertEquals(
            [
                ...Oauth2OidcScopeCollectionInterface::OPENID_CONNECT_DEFAULT_SCOPES,
                'custom-scope',
            ],
            $response['scopes_supported']
        );
        $this->assertEquals($expectedClaims, $response['claims_supported']);
        $this->assertEquals(
            [
                'code',
                'token',
                'code token',
            ],
            $response['response_types_supported']
        );
        $this->assertEquals(
            [
                'public',
            ],
            $response['subject_types_supported']
        );
        $this->assertEquals(
            [
                'RS256',
            ],
            $response['id_token_signing_alg_values_supported']
        );
        $this->assertEquals(
            [
                'client_secret_basic',
                'client_secret_post',
            ],
            $response['token_endpoint_auth_methods_supported']
        );
        $this->assertEquals($serviceDocumentationTestUrl, $response['service_documentation']);
    }

    public function testRunCustomUserInfoEndpoint()
    {
        $openIdConnectUserinfoEndpoint = 'https://customUserinfoEndpoint';
        $this->mockWebApplication([
            'modules' => [
                'oauth2' => [
                    'openIdConnectUserinfoEndpoint' => $openIdConnectUserinfoEndpoint,
                ],
            ],
        ]);

        Yii::$app->controller = $this->getMockController();
        $configurationAction = new Oauth2OpenidConfigurationAction('configuration', Yii::$app->controller);
        $response = $configurationAction->run();

        $this->assertEquals($openIdConnectUserinfoEndpoint, $response['userinfo_endpoint']);
    }

    public function testRunOpenIdConnectDisabled()
    {
        $this->mockWebApplication([
            'modules' => [
                'oauth2' => [
                    'enableOpenIdConnect' => false,
                ],
            ],
        ]);

        $configurationAction = new Oauth2OpenidConfigurationAction('configuration', $this->getMockController());

        $this->expectExceptionMessage('OpenID Connect is disabled');
        $configurationAction->run();
    }
}
