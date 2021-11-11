<?php

namespace rhertogh\Yii2Oauth2Server\exceptions;

use League\OAuth2\Server\Exception\OAuthServerException;

/**
 * @see https://openid.net/specs/openid-connect-core-1_0.html#AuthError
 */
class Oauth2OidcServerException extends OAuthServerException
{
    /**
     * Login Required error.
     * @param null|string $redirectUri A HTTP URI to redirect the user back to
     * @return static
     * @see https://openid.net/specs/openid-connect-core-1_0.html#AuthError
     * @since 1.0.0
     * @since 1.0.0
     */
    public static function loginRequired($redirectUri = null)
    {
        $errorMessage = 'User login is required';
        $hint = 'User authentication is required but the "prompt" parameter is set to "none".';

        return new static($errorMessage, 0, 'login_required', 400, $hint, $redirectUri);
    }

    /**
     * Interaction Required error.
     * @param null|string $redirectUri A HTTP URI to redirect the user back to
     * @return static
     * @see https://openid.net/specs/openid-connect-core-1_0.html#AuthError
     * @since 1.0.0
     */
    public static function interactionRequired($redirectUri = null)
    {
        $errorMessage = 'User interaction is required';
        $hint = 'User interaction is required but the "prompt" parameter is set to "none".';

        return new static($errorMessage, 0, 'interaction_required', 400, $hint, $redirectUri);
    }

    /**
     * Consent Required error.
     * @param null|string $redirectUri A HTTP URI to redirect the user back to
     * @return static
     * @see https://openid.net/specs/openid-connect-core-1_0.html#AuthError
     * @since 1.0.0
     */
    public static function consentRequired($redirectUri = null)
    {
        $errorMessage = 'User consent is required';
        $hint = 'User consent is required but the "prompt" parameter is set to "none".';

        return new static($errorMessage, 0, 'consent_required', 400, $hint, $redirectUri);
    }

    /**
     * Account Selection Required error.
     * @param null|string $redirectUri A HTTP URI to redirect the user back to
     * @return static
     * @see https://openid.net/specs/openid-connect-core-1_0.html#AuthError
     * @since 1.0.0
     */
    public static function accountSelectionRequired($hint = null, $redirectUri = null)
    {
        $errorMessage = 'User account selection is required';
        return new static($errorMessage, 0, 'account_selection_required', 400, $hint, $redirectUri);
    }

    /**
     * "request" parameter is not supported error.
     * @param null|string $redirectUri A HTTP URI to redirect the user back to
     * @return static
     * @see https://openid.net/specs/openid-connect-core-1_0.html#AuthError
     * @since 1.0.0
     */
    public static function requestParameterNotSupported($redirectUri = null)
    {
        $errorMessage = 'The use of the "request" parameter is not supported';
        $hint = 'Try to send the request as query parameters.';

        return new static($errorMessage, 0, 'request_not_supported', 400, $hint, $redirectUri);
    }

    /**
     * "request_uri" parameter is not supported error.
     * @param null|string $redirectUri A HTTP URI to redirect the user back to
     * @return static
     * @see https://openid.net/specs/openid-connect-core-1_0.html#AuthError
     * @since 1.0.0
     */
    public static function requestUriParameterNotSupported($redirectUri = null)
    {
        $errorMessage = 'The use of the "request_uri" parameter is not supported';
        $hint = 'Try to send the request as query parameters.';

        return new static($errorMessage, 0, 'request_uri_not_supported', 400, $hint, $redirectUri);
    }
}
