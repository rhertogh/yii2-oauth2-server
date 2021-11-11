<?php

namespace rhertogh\Yii2Oauth2Server\interfaces\components\server;

use League\OAuth2\Server\AuthorizationValidators\AuthorizationValidatorInterface;
use League\OAuth2\Server\CryptKey;
use League\OAuth2\Server\Exception\OAuthServerException;
use League\OAuth2\Server\Repositories\AccessTokenRepositoryInterface;
use Psr\Http\Message\ServerRequestInterface;

interface Oauth2ResourceServerInterface
{
    /**
     * New server instance.
     * @param AccessTokenRepositoryInterface $accessTokenRepository
     * @param CryptKey|string $publicKey
     * @param null|AuthorizationValidatorInterface $authorizationValidator
     * @since 1.0.0
     */
    public function __construct(
        AccessTokenRepositoryInterface $accessTokenRepository,
        $publicKey,
        AuthorizationValidatorInterface $authorizationValidator = null
    );

    /**
     * Determine the access token validity.
     * @param ServerRequestInterface $request
     * @return ServerRequestInterface
     * @throws OAuthServerException
     * @since 1.0.0
     */
    public function validateAuthenticatedRequest(ServerRequestInterface $request);
}
