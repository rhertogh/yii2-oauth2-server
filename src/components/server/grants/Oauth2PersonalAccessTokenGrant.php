<?php

namespace rhertogh\Yii2Oauth2Server\components\server\grants;

use DateInterval;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Entities\UserEntityInterface;
use League\OAuth2\Server\Exception\OAuthServerException;
use League\OAuth2\Server\Grant\AbstractGrant;
use League\OAuth2\Server\RequestEvent;
use League\OAuth2\Server\ResponseTypes\ResponseTypeInterface;
use Psr\Http\Message\ServerRequestInterface;
use rhertogh\Yii2Oauth2Server\components\server\grants\traits\Oauth2GrantTrait;
use rhertogh\Yii2Oauth2Server\exceptions\Oauth2ServerException;
use rhertogh\Yii2Oauth2Server\interfaces\components\repositories\Oauth2AccessTokenRepositoryInterface;
use rhertogh\Yii2Oauth2Server\interfaces\components\repositories\Oauth2UserRepositoryInterface;
use rhertogh\Yii2Oauth2Server\interfaces\components\server\grants\Oauth2PersonalAccessTokenGrantInterface;

class Oauth2PersonalAccessTokenGrant extends AbstractGrant implements Oauth2PersonalAccessTokenGrantInterface
{
    use Oauth2GrantTrait;

    /**
     * @var Oauth2UserRepositoryInterface
     */
    protected $userRepository;

    /**
     * @param Oauth2AccessTokenRepositoryInterface     $accessTokenRepository
     *
     * @throws \Exception
     */
    public function __construct(
        Oauth2UserRepositoryInterface        $userRepository,
        Oauth2AccessTokenRepositoryInterface $accessTokenRepository
    ) {
        $this->setUserRepository($userRepository);
        $this->setAccessTokenRepository($accessTokenRepository);
    }

    public function getIdentifier()
    {
        return 'personal_access_token';
    }

    public function respondToAccessTokenRequest(ServerRequestInterface $request, ResponseTypeInterface $responseType, DateInterval $accessTokenTTL)
    {
        $client = $this->validateClient($request);
        $user = $this->validateUser($request);
        $scopes = $this->validateScopes($this->getRequestParameter('scope', $request));

        $scopes = $this->scopeRepository->finalizeScopes(
            $scopes,
            $this->getIdentifier(),
            $client,
            $user->getIdentifier(),
        );

        $accessToken = $this->issueAccessToken(
            $accessTokenTTL,
            $client,
            $user->getIdentifier(),
            $scopes
        );

        $responseType->setAccessToken($accessToken);

        return $responseType;
    }

    /**
     * Validate the client.
     *
     * @param ServerRequestInterface $request
     *
     * @throws OAuthServerException
     *
     * @return ClientEntityInterface
     */
    protected function validateClient(ServerRequestInterface $request)
    {
        [$clientId, $clientSecret] = $this->getClientCredentials($request);

        if ($this->clientRepository->validateClient($clientId, $clientSecret, $this->getIdentifier()) === false) {
            $this->getEmitter()->emit(new RequestEvent(RequestEvent::CLIENT_AUTHENTICATION_FAILED, $request));
            throw OAuthServerException::invalidClient($request);
        }

        return $this->getClientEntityOrFail($clientId, $request);
    }

    /**
     * @param ServerRequestInterface $request
     *
     * @return UserEntityInterface
     *@throws OAuthServerException
     *
     */
    protected function validateUser(ServerRequestInterface $request)
    {
        $userIdentifier = $this->getRequestParameter('user_id', $request);

        if (empty($userIdentifier)) {
            throw OAuthServerException::invalidRequest('user_id');
        }

        $user = $this->userRepository->getUserEntityByIdentifier($userIdentifier);

        if (empty($user)) {
            throw Oauth2ServerException::accessDenied();
        }

        return $user;
    }


}
