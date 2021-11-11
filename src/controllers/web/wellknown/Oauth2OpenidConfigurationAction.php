<?php

namespace rhertogh\Yii2Oauth2Server\controllers\web\wellknown;

use League\OAuth2\Server\Grant\AuthCodeGrant;
use League\OAuth2\Server\Grant\ImplicitGrant;
use rhertogh\Yii2Oauth2Server\controllers\web\Oauth2WellKnownController;
use rhertogh\Yii2Oauth2Server\interfaces\components\openidconnect\request\Oauth2OidcAuthenticationRequestInterface;
use rhertogh\Yii2Oauth2Server\interfaces\controllers\web\Oauth2CertificatesControllerInterface;
use rhertogh\Yii2Oauth2Server\interfaces\controllers\web\Oauth2OidcControllerInterface;
use rhertogh\Yii2Oauth2Server\interfaces\controllers\web\Oauth2ServerControllerInterface;
use Yii;
use yii\base\Action;
use yii\helpers\Url;
use yii\web\ForbiddenHttpException;

/**
 * @property Oauth2WellKnownController $controller
 */
class Oauth2OpenidConfigurationAction extends Action
{
    /**
     *
     */
    public function run()
    {
        $module = $this->controller->module;

        if (!$module->enableOpenIdConnect) {
            throw new ForbiddenHttpException('OpenID Connect is disabled.');
        }

        $supportedScopeAndClaimIdentifiers = $module->getOidcScopeCollection()
            ->getSupportedScopeAndClaimIdentifiers();

        $responseTypes = [];
        foreach ($module->getAuthorizationServer()->getEnabledGrantTypes() as $grantType) {
            if ($grantType instanceof AuthCodeGrant) {
                $responseTypes[] = 'code';
            } elseif ($grantType instanceof ImplicitGrant) {
                $responseTypes[] = 'token';
            }
        }
        $responseTypes = array_unique($responseTypes);
        $responseTypeCombinations = [];
        foreach ($responseTypes as $responseType) {
            $newCombinations = [$responseType];
            foreach ($responseTypeCombinations as $responseTypeCombination) {
                $newCombinations[] = $responseTypeCombination . ' ' . $responseType;
            }
            $responseTypeCombinations = array_merge($responseTypeCombinations, $newCombinations);
        }

        $authorizationEndpoint = Url::to([
            Oauth2ServerControllerInterface::CONTROLLER_NAME
                . '/' . Oauth2ServerControllerInterface::ACTION_NAME_AUTHORIZE
            ],
            true
        );
        $tokenEndpoint = Url::to([
            Oauth2ServerControllerInterface::CONTROLLER_NAME
                . '/' . Oauth2ServerControllerInterface::ACTION_NAME_ACCESS_TOKEN,
            ],
            true
        );
        $jwksUri = Url::to([
            Oauth2CertificatesControllerInterface::CONTROLLER_NAME
                . '/' . Oauth2CertificatesControllerInterface::ACTION_NAME_JWKS,
            ],
            true
        );

        // https://openid.net/specs/openid-connect-discovery-1_0.html#rfc.section.3
        $openIdConfig = [
            'issuer' => Yii::$app->request->getHostInfo(),
            'authorization_endpoint' => $authorizationEndpoint,
            'token_endpoint' => $tokenEndpoint
        ];

        // Add 'userinfo_endpoint' if configured
        if (!empty($module->openIdConnectUserinfoEndpoint)) {
            if ($module->openIdConnectUserinfoEndpoint === true) {
                $openIdConfig['userinfo_endpoint'] = Url::to([
                    Oauth2OidcControllerInterface::CONTROLLER_NAME
                        . '/' . Oauth2OidcControllerInterface::ACTION_NAME_USERINFO,
                    ],
                    true
                );
            } else {
                $openIdConfig['userinfo_endpoint'] = $module->openIdConnectUserinfoEndpoint;
            }
        }

        $openIdConfig += [
            'jwks_uri' => $jwksUri,
            'scopes_supported' => $supportedScopeAndClaimIdentifiers['scopeIdentifiers'],
            'claims_supported' => $supportedScopeAndClaimIdentifiers['claimIdentifiers'],
            'response_types_supported' => $responseTypeCombinations,
        ];

        if ($module->openIdConnectDiscoveryIncludeSupportedGrantTypes) {
            $enabledGrantTypes = $module->getAuthorizationServer()->getEnabledGrantTypes();
            $supportedGrantTypes = [];
            foreach ($enabledGrantTypes as $grantType) {
                $grantTypeIdentifier = $grantType->getIdentifier();
                if (in_array($grantTypeIdentifier, Oauth2OidcAuthenticationRequestInterface::SUPPORTED_AUTHENTICATION_FLOWS)) {
                    $supportedGrantTypes[] = $grantTypeIdentifier;
                }
            }
            $openIdConfig['grant_types_supported'] = $supportedGrantTypes;
        }

        $openIdConfig += [
            'subject_types_supported' => [
                'public',
            ],
            'id_token_signing_alg_values_supported' => [
                'RS256',
            ],
            'token_endpoint_auth_methods_supported' => [
                'client_secret_basic',
                'client_secret_post',
            ],
        ];

        if (!empty($module->openIdConnectDiscoveryServiceDocumentationUrl)) {
            $openIdConfig['service_documentation'] = $module->openIdConnectDiscoveryServiceDocumentationUrl;
        }

        $openIdConfig += [
            'claims_parameter_supported' => false, //ToDo: set to `true` when the 'claims' parameter is supported.
            'request_parameter_supported' => false, //ToDo: set to `true` when the 'request' parameter is supported.
        ];

        return $openIdConfig;
    }
}
