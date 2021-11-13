<?php

namespace rhertogh\Yii2Oauth2Server\controllers\web\openidconnect;

use rhertogh\Yii2Oauth2Server\controllers\web\Oauth2OidcController;
use rhertogh\Yii2Oauth2Server\interfaces\components\openidconnect\request\Oauth2OidcAuthenticationRequestInterface;
use rhertogh\Yii2Oauth2Server\interfaces\components\openidconnect\scope\Oauth2OidcClaimInterface;
use rhertogh\Yii2Oauth2Server\interfaces\components\openidconnect\scope\Oauth2OidcScopeInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\Oauth2OidcUserInterface;
use rhertogh\Yii2Oauth2Server\Oauth2Module;
use Yii;
use yii\base\Action;
use yii\base\InvalidConfigException;
use yii\web\ForbiddenHttpException;
use yii\web\Response;

/**
 * @property Oauth2OidcController $controller
 */
class Oauth2OidcUserinfoAction extends Action
{
    /**
     * @see https://openid.net/specs/openid-connect-core-1_0.html#UserInfo
     * @return Response
     */
    public function run()
    {
        $module = $this->controller->module;
        $response = Yii::$app->response;

        if (!$module->enableOpenIdConnect) {
            throw new ForbiddenHttpException('OpenID Connect is disabled.');
        }

        if (!$module->requestHasScope(Oauth2OidcScopeInterface::OPENID_CONNECT_SCOPE_OPENID)) {
            throw new ForbiddenHttpException(
                'Request authentication does not contain the required OpenID Connect "openid" scope.'
            );
        }

        /** @var Oauth2OidcUserInterface $identity */
        $identity = $this->controller->module->getUserIdentity();

        $nonce = Yii::$app->request->post(Oauth2OidcAuthenticationRequestInterface::REQUEST_PARAMETER_NONCE)
            ?? Yii::$app->request->get(Oauth2OidcAuthenticationRequestInterface::REQUEST_PARAMETER_NONCE);

        $clientIdentifier = $module->getRequestOauthClientIdentifier();
        $client = $module->getClientRepository()->getClientEntity($clientIdentifier);

        if (empty($client) || !$client->isEnabled()) {
            throw new ForbiddenHttpException('Client "' . $clientIdentifier . '" not found or disabled.');
        }

        $userInfoAlg = $client->getOpenIdConnectUserinfoEncryptedResponseAlg();

        if (empty($userInfoAlg)) {
            $response->format = Response::FORMAT_JSON;
            $response->data = $this->generateOpenIdConnectUserClaims($identity, $module, $nonce);
            return $response;
        } elseif ($userInfoAlg == 'RS256') {
            $response->format = Response::FORMAT_RAW;
            $response->headers->add('Content-Type', 'application/jwt');
            $response->data = $module
                ->generateOpenIdConnectUserClaimsToken(
                    $identity,
                    $module->getRequestOauthClientIdentifier(),
                    $module->getPrivateKey(),
                    $module->getRequestOauthScopeIdentifiers(),
                    $nonce,
                )
                ->toString();

            return $response;
        } else {
            throw new InvalidConfigException('Unknown userinfo response algorithm "' . $userInfoAlg . '".');
        }
    }

    /**
     * @param Oauth2OidcUserInterface $user
     * @param Oauth2Module $module
     * @return array
     * @throws InvalidConfigException
     */
    protected function generateOpenIdConnectUserClaims($user, $module, $nonce)
    {
        $oidcScopeCollection = $module->getOidcScopeCollection();
        $claims = $oidcScopeCollection->getFilteredClaims($module->getRequestOauthScopeIdentifiers());

        $userClaims = [];
        foreach ($claims as $claim) {
            $claimIdentifier = $claim->getIdentifier();
            if ($claimIdentifier == Oauth2OidcClaimInterface::OPENID_CONNECT_CLAIM_SUB) {
                $claimValue = $user->getIdentifier();
            } elseif ($claimIdentifier == Oauth2OidcClaimInterface::OPENID_CONNECT_CLAIM_AUTH_TIME) {
                $claimValue = $user->getLatestAuthenticatedAt();
            } elseif ($claimIdentifier == Oauth2OidcClaimInterface::OPENID_CONNECT_CLAIM_NONCE) {
                if (!empty($nonce)) {
                    $claimValue = $nonce;
                } else {
                    continue;
                }
            } else {
                $claimValue = $user->getOpenIdConnectClaimValue($claim, $module);
            }
            $userClaims[$claimIdentifier] = $claimValue;
        }

        return $userClaims;
    }
}
