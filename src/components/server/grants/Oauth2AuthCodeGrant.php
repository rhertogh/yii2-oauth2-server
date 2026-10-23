<?php

namespace rhertogh\Yii2Oauth2Server\components\server\grants;

use DateInterval;
use League\OAuth2\Server\Entities\AccessTokenEntityInterface;
use League\OAuth2\Server\Entities\RefreshTokenEntityInterface;
use League\OAuth2\Server\Grant\AuthCodeGrant;
use League\OAuth2\Server\Repositories\AuthCodeRepositoryInterface;
use League\OAuth2\Server\Repositories\RefreshTokenRepositoryInterface;
use rhertogh\Yii2Oauth2Server\components\server\grants\traits\Oauth2GrantTrait;
use rhertogh\Yii2Oauth2Server\interfaces\components\openidconnect\scope\Oauth2OidcScopeInterface;
use rhertogh\Yii2Oauth2Server\interfaces\components\server\grants\Oauth2AuthCodeGrantInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\Oauth2AccessTokenInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\Oauth2RefreshTokenInterface;

class Oauth2AuthCodeGrant extends AuthCodeGrant implements Oauth2AuthCodeGrantInterface
{
    use Oauth2GrantTrait {
        issueRefreshToken as issueRefreshTokenInternal;
    }

    public function __construct(AuthCodeRepositoryInterface $authCodeRepository, RefreshTokenRepositoryInterface $refreshTokenRepository, DateInterval $authCodeTTL)
    {
        parent::__construct($authCodeRepository, $refreshTokenRepository, $authCodeTTL);

        // PKCE is independent of the Client type (both public and confidential clients should have it, https://oauth.net/2/pkce/).
        // It is handled by the Oauth2ClientInterface::isAuthCodeWithoutPkceAllowed() functionality instead.
        $this->disableRequireCodeChallengeForPublicClients();
    }

    /**
     * @inheritDoc
     * @param Oauth2AccessTokenInterface|AccessTokenEntityInterface $accessToken
     * @return Oauth2RefreshTokenInterface|RefreshTokenEntityInterface|void|null
     */
    protected function issueRefreshToken(AccessTokenEntityInterface $accessToken): ?RefreshTokenEntityInterface
    {
        if (
            $this->module->enableOpenIdConnect
            && !$this->module->openIdConnectIssueRefreshTokenWithoutOfflineAccessScope
        ) {
            $scopeIdentifiers = array_map(fn($scope) => $scope->getIdentifier(), $accessToken->getScopes());

            if (
                in_array(Oauth2OidcScopeInterface::OPENID_CONNECT_SCOPE_OPENID, $scopeIdentifiers)
                && !in_array(Oauth2OidcScopeInterface::OPENID_CONNECT_SCOPE_OFFLINE_ACCESS, $scopeIdentifiers)
            ) {
                // Don't issue refresh token when offline access scope is not authorized.
                return null;
            }
        }

        return $this->issueRefreshTokenInternal($accessToken);
    }
}
