<?php

namespace rhertogh\Yii2Oauth2Server\components\server\grants;

use DateInterval;
use League\OAuth2\Server\Exception\OAuthServerException;
use League\OAuth2\Server\Grant\RefreshTokenGrant;
use League\OAuth2\Server\ResponseTypes\ResponseTypeInterface;
use Psr\Http\Message\ServerRequestInterface;
use rhertogh\Yii2Oauth2Server\components\server\grants\traits\Oauth2GrantTrait;
use rhertogh\Yii2Oauth2Server\exceptions\Oauth2ServerException;
use rhertogh\Yii2Oauth2Server\interfaces\components\openidconnect\scope\Oauth2OidcScopeInterface;
use rhertogh\Yii2Oauth2Server\interfaces\components\server\grants\Oauth2RefreshTokenGrantInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\external\user\Oauth2OidcUserSessionStatusInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\Oauth2ClientInterface;
use rhertogh\Yii2Oauth2Server\Oauth2Module;
use Yii;
use yii\base\InvalidConfigException;
use yii\web\NotFoundHttpException;

class Oauth2RefreshTokenGrant extends RefreshTokenGrant implements Oauth2RefreshTokenGrantInterface
{
    use Oauth2GrantTrait;

    /** @var Oauth2Module */
    public $module;

    /**
     * @throws InvalidConfigException
     * @throws Oauth2ServerException
     * @throws NotFoundHttpException
     * @throws OAuthServerException
     */
    public function respondToAccessTokenRequest(
        ServerRequestInterface $request,
        ResponseTypeInterface $responseType,
        DateInterval $accessTokenTTL
    ) {
        /** @var Oauth2ClientInterface $client */
        $client = $this->validateClient($request);
        $oldRefreshToken = $this->validateOldRefreshToken($request, $client->getIdentifier());

        $user = $this->module->getUserRepository()->getUserEntityByIdentifier($oldRefreshToken['user_id']);
        if (empty($user)) {
            throw new NotFoundHttpException(
                Yii::t('oauth2', 'Unable to find user with id "' . $oldRefreshToken['user_id'] . '".')
            );
        }

        if ($user->isOauth2ClientAllowed($client, $this->getIdentifier()) !== true) {
            throw Oauth2ServerException::accessDenied(
                Yii::t('oauth2', 'User {user_id} is not allowed to use client {client_identifier}.', [
                    'user_id' => $user->getId(),
                    'client_identifier' => $client->getIdentifier(),
                ])
            );
        }

        if ($this->module->enableOpenIdConnect) {
            $scopes = $oldRefreshToken['scopes'] ?? [];

            if (
                in_array(Oauth2OidcScopeInterface::OPENID_CONNECT_SCOPE_OPENID, $scopes)
                && !in_array(Oauth2OidcScopeInterface::OPENID_CONNECT_SCOPE_OFFLINE_ACCESS, $scopes)
            ) {
                // Refresh tokens are not issued when `openIdConnectIssueRefreshTokenWithoutOfflineAccessScope`
                // is disabled, but let's ensure setting hasn't changed.
                if (!$this->module->openIdConnectIssueRefreshTokenWithoutOfflineAccessScope) {
                    throw Oauth2ServerException::accessDenied(
                        'The refresh token grant type is unavailable in OpenID Connect context when'
                            . ' `openIdConnectIssueRefreshTokenWithoutOfflineAccessScope` is disabled.'
                    );
                }

                if (!($user instanceof Oauth2OidcUserSessionStatusInterface)) {
                    throw new InvalidConfigException(
                        'In order to support OpenId Connect Refresh Tokens without offline_access scope '
                            . get_class($user) . ' must implement ' . Oauth2OidcUserSessionStatusInterface::class
                    );
                }

                if (!$user->hasActiveSession()) {
                    throw Oauth2ServerException::accessDenied(
                        'The refresh token grant type is unavailable in OpenID Connect context when the user is'
                        . ' offline and the authorized scope does not include "offline_access".'
                    );
                }
            }
        }

        return parent::respondToAccessTokenRequest($request, $responseType, $accessTokenTTL);
    }
}
