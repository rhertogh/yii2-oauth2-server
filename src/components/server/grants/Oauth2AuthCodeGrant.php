<?php

namespace rhertogh\Yii2Oauth2Server\components\server\grants;

use League\OAuth2\Server\Entities\AccessTokenEntityInterface;
use League\OAuth2\Server\Entities\RefreshTokenEntityInterface;
use League\OAuth2\Server\Grant\AuthCodeGrant;
use rhertogh\Yii2Oauth2Server\interfaces\components\openidconnect\scope\Oauth2OidcScopeInterface;
use rhertogh\Yii2Oauth2Server\interfaces\components\server\grants\Oauth2AuthCodeGrantInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\Oauth2AccessTokenInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\Oauth2RefreshTokenInterface;
use rhertogh\Yii2Oauth2Server\Oauth2Module;

class Oauth2AuthCodeGrant extends AuthCodeGrant implements Oauth2AuthCodeGrantInterface
{
    /** @var Oauth2Module */
    public $module;

    /**
     * @inheritDoc
     * @param Oauth2AccessTokenInterface|AccessTokenEntityInterface $accessToken
     * @return Oauth2RefreshTokenInterface|RefreshTokenEntityInterface|void|null
     */
    protected function issueRefreshToken(AccessTokenEntityInterface $accessToken)
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

        return parent::issueRefreshToken($accessToken);
    }
}
