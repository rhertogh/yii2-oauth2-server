<?php

namespace rhertogh\Yii2Oauth2Server\exceptions;

use League\OAuth2\Server\Exception\OAuthServerException;
use rhertogh\Yii2Oauth2Server\interfaces\exceptions\Oauth2ServerExceptionInterface;

class Oauth2ServerException extends OAuthServerException implements Oauth2ServerExceptionInterface
{
    /**
     * Authorization not allowed.
     * @param null|string $redirectUri A HTTP URI to redirect the user back to
     * @return static
     * @since 1.0.0
     */
    public static function authorizationNotAllowed($redirectUri = null)
    {
        $errorMessage = 'Authorization not allowed.';
        $hint = 'The user is not is not allowed to authorize the specified client.';

        return new static($errorMessage, 0, 'authorization_not_allowed', 403, $hint, $redirectUri);
    }

    /**
     * Unknown scope error.
     *
     * @param string      $scope       The bad scope
     * @param null|string $redirectUri A HTTP URI to redirect the user back to
     *
     * @return static
     */
    public static function unknownScope($scope, $redirectUri = null)
    {
        $errorMessage = 'The requested scope is unknown.';

        $hint = \sprintf(
            'Check the spelling of the `%s` scope or remove it from the request.',
            \htmlspecialchars($scope, ENT_QUOTES, 'UTF-8', false)
        );

        return new static($errorMessage, 5, 'scope_not_allowed_for_client', 403, $hint, $redirectUri);
    }

    /**
     * Unauthorized scope error.
     *
     * @param string      $scope       The bad scope
     * @param null|string $redirectUri A HTTP URI to redirect the user back to
     *
     * @return static
     */
    public static function scopeNotAllowedForClient($scope, $redirectUri = null)
    {
        $errorMessage = 'The requested scope is not allowed for the specified client.';

        $hint = \sprintf(
            'Request access to the `%s` scope for the client or remove it from the request.',
            \htmlspecialchars($scope, ENT_QUOTES, 'UTF-8', false)
        );

        return new static($errorMessage, 5, 'scope_not_allowed_for_client', 403, $hint, $redirectUri);
    }
}
