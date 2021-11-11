<?php

namespace rhertogh\Yii2Oauth2Server\components\server\grants;

use DateInterval;
use League\OAuth2\Server\Exception\OAuthServerException;
use League\OAuth2\Server\Grant\RefreshTokenGrant;
use League\OAuth2\Server\ResponseTypes\ResponseTypeInterface;
use Psr\Http\Message\ServerRequestInterface;
use rhertogh\Yii2Oauth2Server\interfaces\components\openidconnect\scope\Oauth2OidcScopeInterface;
use rhertogh\Yii2Oauth2Server\interfaces\components\server\grants\Oauth2RefreshTokenGrantInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\Oauth2OidcUserInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\Oauth2OidcUserSessionStatusInterface;
use rhertogh\Yii2Oauth2Server\Oauth2Module;
use Yii;
use yii\base\InvalidConfigException;
use yii\web\NotFoundHttpException;

class Oauth2RefreshTokenGrant extends RefreshTokenGrant implements Oauth2RefreshTokenGrantInterface
{
    /** @var Oauth2Module */
    public $module;

    public function respondToAccessTokenRequest(
        ServerRequestInterface $request,
        ResponseTypeInterface $responseType,
        DateInterval $accessTokenTTL
    ) {

        if ($this->module->enableOpenIdConnect) {

            $client = $this->validateClient($request);
            $oldRefreshToken = $this->validateOldRefreshToken($request, $client->getIdentifier());
            $scopes = $oldRefreshToken['scopes'] ?? [];

            if (in_array(Oauth2OidcScopeInterface::OPENID_CONNECT_SCOPE_OPENID, $scopes)
                && !in_array(Oauth2OidcScopeInterface::OPENID_CONNECT_SCOPE_OFFLINE_ACCESS, $scopes)
            ) {
                // Refresh tokens are not issued when `openIdConnectIssueRefreshTokenWithoutOfflineAccessScope` is disabled, but let's ensure setting hasn't changed
                if (!$this->module->openIdConnectIssueRefreshTokenWithoutOfflineAccessScope) {
                    throw OAuthServerException::accessDenied('The refresh token grant type is unavailable in OpenID Connect context when `openIdConnectIssueRefreshTokenWithoutOfflineAccessScope` is disabled.');
                }

                $user = $this->module->getUserRepository()->getUserEntityByIdentifier($oldRefreshToken['user_id']);
                if (empty($user)) {
                    throw new NotFoundHttpException( Yii::t('oauth2', 'Unable to find user with id "' . $oldRefreshToken['user_id'] . '".'));
                }
                if (!($user instanceof Oauth2OidcUserSessionStatusInterface)) {
                    throw new InvalidConfigException('In order to support OpenId Connect Refresh Tokens without offline_access scope '
                        . get_class($user) . ' must implement ' . Oauth2OidcUserSessionStatusInterface::class);
                }

                if (!$user->hasActiveSession()) {
                    throw OAuthServerException::accessDenied('The refresh token grant type is unavailable in OpenID Connect context when the user is offline and the authorized scope does not include "offline_access".');
                }
            }
        }

        return parent::respondToAccessTokenRequest($request, $responseType, $accessTokenTTL);
    }
}
