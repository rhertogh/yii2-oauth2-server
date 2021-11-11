<?php

namespace rhertogh\Yii2Oauth2Server\interfaces\components\server;

use DateInterval;
use Defuse\Crypto\Key;
use League\Event\EmitterAwareInterface;
use League\OAuth2\Server\CryptKey;
use League\OAuth2\Server\Exception\OAuthServerException;
use League\OAuth2\Server\Grant\GrantTypeInterface;
use League\OAuth2\Server\Repositories\AccessTokenRepositoryInterface;
use League\OAuth2\Server\Repositories\ClientRepositoryInterface;
use League\OAuth2\Server\Repositories\ScopeRepositoryInterface;
use League\OAuth2\Server\RequestTypes\AuthorizationRequest;
use League\OAuth2\Server\ResponseTypes\ResponseTypeInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

interface Oauth2AuthorizationServerInterface extends EmitterAwareInterface
{
    /**
     * New server instance.
     * @param ClientRepositoryInterface      $clientRepository
     * @param AccessTokenRepositoryInterface $accessTokenRepository
     * @param ScopeRepositoryInterface       $scopeRepository
     * @param CryptKey|string                $privateKey
     * @param string|Key                     $encryptionKey
     * @param null|ResponseTypeInterface     $responseType
     * @since 1.0.0
     */
    public function __construct(
        ClientRepositoryInterface $clientRepository,
        AccessTokenRepositoryInterface $accessTokenRepository,
        ScopeRepositoryInterface $scopeRepository,
        $privateKey,
        $encryptionKey,
        ResponseTypeInterface $responseType = null
    );

    /**
     * Enable a grant type on the server.
     * @param GrantTypeInterface $grantType
     * @param null|DateInterval $accessTokenTTL
     * @since 1.0.0
     */
    public function enableGrantType(GrantTypeInterface $grantType, DateInterval $accessTokenTTL = null);

    /**
     * Validate an authorization request
     * @param ServerRequestInterface $request
     * @return AuthorizationRequest
     * @throws OAuthServerException
     * @since 1.0.0
     */
    public function validateAuthorizationRequest(ServerRequestInterface $request);

    /**
     * Complete an authorization request
     * @param AuthorizationRequest $authRequest
     * @param ResponseInterface $response
     * @return ResponseInterface
     * @since 1.0.0
     */
    public function completeAuthorizationRequest(AuthorizationRequest $authRequest, ResponseInterface $response);

    /**
     * Return an access token response.
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @return ResponseInterface
     * @throws OAuthServerException
     * @since 1.0.0
     */
    public function respondToAccessTokenRequest(ServerRequestInterface $request, ResponseInterface $response);

    /**
     * Set the default scope for the authorization server.
     * @param string $defaultScope
     * @since 1.0.0
     */
    public function setDefaultScope($defaultScope);

    /**
     * Sets whether to revoke refresh tokens or not (for all grant types).
     * @param bool $revokeRefreshTokens
     * @since 1.0.0
     */
    public function revokeRefreshTokens(bool $revokeRefreshTokens): void;

    /**
     * Get the enabled grand types for the authorization server.
     * @return GrantTypeInterface[]
     * @since 1.0.0
     */
    public function getEnabledGrantTypes();
}
