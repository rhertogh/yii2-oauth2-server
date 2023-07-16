<?php

namespace Yii2Oauth2ServerTests\unit\controllers\console\debug;

use rhertogh\Yii2Oauth2Server\controllers\console\debug\Oauth2DebugConfigAction;
use rhertogh\Yii2Oauth2Server\controllers\console\Oauth2DebugController;
use rhertogh\Yii2Oauth2Server\Oauth2Module;
use yii\console\ExitCode;
use Yii2Oauth2ServerTests\unit\TestCase;

/**
 * @covers \rhertogh\Yii2Oauth2Server\controllers\console\debug\Oauth2DebugConfigAction
 */
class Oauth2DebugConfigActionTest extends TestCase
{
    protected function getMockController($config = [], $moduleConfig = [])
    {
        $this->mockConsoleApplication([
            'modules' => [
                'oauth2' => $moduleConfig,
            ],
        ]);

        return new class ('debug', Oauth2Module::getInstance(), $config) extends Oauth2DebugController {
            public function stdout($string)
            {
                echo $string;
            }

            public function stderr($string)
            {
                echo $string;
            }
        };
    }

    public function testRunOK()
    {
        $controller = $this->getMockController();
        $action = new Oauth2DebugConfigAction('debug-config', $controller);

        $this->assertEquals(ExitCode::OK, $action->run());
    }

    /**
     * @param $moduleConfig
     * @dataProvider getConfigurationProvider
     */
    public function testGetConfiguration($moduleConfig)
    {
        $getterProperties = [
            'defaultAccessTokenTTL',
        ];

        $ignoreModuleProperties = [
            'appType', // Irrelevant for the cli actions (always console).
            'controllerNamespace', // Only defined in module to override default Yii behavior.
            'migrationsFileMode', // Only used for local development.
            'migrationsFileOwnership', // Only used for local development.
            'migrationsNamespace', // Only used for local development.
            'migrationsPrefix', // Only used for local development.
        ];

        $controller = $this->getMockController([], $moduleConfig);
        $action = new Oauth2DebugConfigAction('debug-config', $controller);
        $configuration = $this->callInaccessibleMethod($action, 'getConfiguration', [Oauth2Module::getInstance()]);
        $configurationProperties = array_keys($configuration);
        sort($configurationProperties);

        $moduleProperties = array_filter(array_map(
            fn(\ReflectionProperty $property) => $property->class == Oauth2Module::class ? $property->name : null,
            (new \ReflectionClass(Oauth2Module::class))->getProperties(\ReflectionProperty::IS_PUBLIC)
        ));

        $moduleProperties = array_diff($moduleProperties, $ignoreModuleProperties);
        $moduleProperties = array_merge($moduleProperties, $getterProperties);
        sort($moduleProperties);

        $this->assertEquals($moduleProperties, $configurationProperties);
    }

    /**
     * @see testGetConfiguration()
     * @return array[]
     */
    public function getConfigurationProvider()
    {
        return [
            [ // Default module test config.
                [],
            ],
            [ // Server role doesn't include "authorization_server".
                [
                    'serverRole' => Oauth2Module::SERVER_ROLE_RESOURCE_SERVER,
                ],
            ],
        ];
    }

    /**
     * @dataProvider getEndpointsProvider
     */
    public function testGetEndpoints($moduleConfig, $overwriteExpectedEndpoints)
    {
        $defaultTestEndpoints = [
            'accessToken' => ['Access Token', 'oauth2/access-token', 'urlRulesPrefix, accessTokenPath'],
            'authorizeClient' => ['Authorize Client', 'oauth2/authorize', 'urlRulesPrefix, authorizePath'],
            'clientAuthorization' =>
                ['Client Authorization', 'oauth2/authorize-client', 'urlRulesPrefix, clientAuthorizationPath'],
            'jwks' => ['JSON Web Key Sets', 'oauth2/certs', 'urlRulesPrefix, jwksPath'],
            'oidcProviderConfigInfo' =>
                ['OpenID Connect Provider Configuration Information', '.well-known/openid-configuration', 'openIdConnectProviderConfigurationInformationPath'],
            'oidcUserinfo' =>
                ['OpenId Connect Userinfo', 'oauth2/oidc/userinfo', 'urlRulesPrefix, openIdConnectUserinfoPath'],
        ];

        $expectedEndpoints = array_merge($defaultTestEndpoints, $overwriteExpectedEndpoints);
        ksort($expectedEndpoints);

        $controller = $this->getMockController([], $moduleConfig);
        $action = new Oauth2DebugConfigAction('debug-config', $controller);
        $endpoints = $this->callInaccessibleMethod($action, 'getEndpoints', [Oauth2Module::getInstance()]);
        ksort($endpoints);

        $this->assertEquals($expectedEndpoints, $endpoints);
    }

    /**
     * @see testGetEndpoints()
     * @return array[]
     */
    public function getEndpointsProvider()
    {
        return [
            'default' => [ // Default module test config.
                [],
                [], // Expect default test endpoints.
            ],
            'server role' => [ // Server role doesn't include "authorization_server".
                [
                    'serverRole' => Oauth2Module::SERVER_ROLE_RESOURCE_SERVER,
                ],
                [
                    'authorizeClient' => ['Authorize Client', '[Only available for "authorization_server" role]', 'serverRole'],
                    'accessToken' => ['Access Token', '[Only available for "authorization_server" role]', 'serverRole'],
                    'jwks' => ['JSON Web Key Sets', '[Only available for "authorization_server" role]', 'serverRole'],
                    'clientAuthorization' =>
                        ['Client Authorization', '[Only available for "authorization_server" role]', 'serverRole'],
                    'oidcProviderConfigInfo' =>
                        ['OpenID Connect Provider Configuration Information', '[Only available for "authorization_server" role]', 'serverRole'],
                    'oidcUserinfo' =>
                        ['OpenId Connect Userinfo', '[Only available for "authorization_server" role]', 'serverRole'],
                ],
            ],
            'OpenID Connect disabled' => [
                [
                    'enableOpenIdConnect' => false,
                ],
                [
                    'oidcProviderConfigInfo' => ['OpenID Connect Provider Configuration Information', '[OpenID Connect is disabled]', 'enableOpenIdConnect'],
                    'oidcUserinfo' => ['OpenId Connect Userinfo', '[OpenID Connect is disabled]', 'enableOpenIdConnect'],
                ],
            ],
            'OpenID Connect Discovery disabled' => [
                [
                    'enableOpenIdConnectDiscovery' => false,
                ],
                [
                    'oidcProviderConfigInfo' => ['OpenID Connect Provider Configuration Information', '[OpenId Connect Discovery is disabled]', 'enableOpenIdConnectDiscovery'],
                ],
            ],
            'OpenID Connect Userinfo disabled' => [
                [
                    'openIdConnectUserinfoEndpoint' => false,
                ],
                [
                    'oidcUserinfo' => ['OpenId Connect Userinfo', '[Userinfo Endpoint is disabled]', 'openIdConnectUserinfoEndpoint'],
                ],
            ],
            'OpenID Connect custom Userinfo endpoint' => [
                [
                    'openIdConnectUserinfoEndpoint' => 'https://custom_openIdConnectUserinfoEndpoint',
                ],
                [
                    'oidcUserinfo' => [
                        'OpenId Connect Userinfo',
                        'https://custom_openIdConnectUserinfoEndpoint',
                        'openIdConnectUserinfoEndpoint'
                    ],
                ],
            ],
        ];
    }
}
