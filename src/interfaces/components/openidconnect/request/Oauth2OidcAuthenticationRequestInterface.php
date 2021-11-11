<?php

namespace rhertogh\Yii2Oauth2Server\interfaces\components\openidconnect\request;

use rhertogh\Yii2Oauth2Server\Oauth2Module;

/**
 * https://openid.net/specs/openid-connect-core-1_0.html#AuthRequest
 */
interface Oauth2OidcAuthenticationRequestInterface
{
    /**
     * Passing Request Parameters as JWTs by passing a Request Object by Value.
     * @see https://openid.net/specs/openid-connect-core-1_0.html#RequestObject
     * @since 1.0.0
     */
    public const REQUEST_PARAMETER_REQUEST = 'request';
    /**
     * Passing Request Parameters as JWTs by passing a Request Object by Reference.
     * https://openid.net/specs/openid-connect-core-1_0.html#RequestUriParameter
     * @since 1.0.0
     */
    public const REQUEST_PARAMETER_REQUEST_URI = 'request_uri';

    /**
     * Optional string value used to associate a Client session with an ID Token, and to mitigate replay attacks.
     * The value is passed through unmodified from the Authentication Request to the ID Token.
     * @since 1.0.0
     */
    public const REQUEST_PARAMETER_NONCE = 'nonce';
    /**
     * Optional space delimited, case-sensitive list of ASCII string values that specifies whether the
     * Authorization Server prompts the End-User for reauthentication and consent
     * @since 1.0.0
     */
    public const REQUEST_PARAMETER_PROMPT = 'prompt';
    /**
     * Optional maximum Authentication Age. Specifies the allowable elapsed time in seconds since the last time the
     * End-User was actively authenticated by the OP. If the elapsed time is greater than this value, the OP MUST
     * attempt to actively re-authenticate the End-User.
     * @since 1.0.0
     */
    public const REQUEST_PARAMETER_MAX_AGE = 'max_age';

    /**
     * Prompt option: The Authorization Server MUST NOT display any authentication or consent user interface pages.
     * @since 1.0.0
     */
    public const REQUEST_PARAMETER_PROMPT_NONE = 'none';
    /**
     * Prompt option: The Authorization Server SHOULD prompt the End-User for reauthentication.
     * @since 1.0.0
     */
    public const REQUEST_PARAMETER_PROMPT_LOGIN = 'login';
    /**
     * Prompt option: The Authorization Server SHOULD prompt the End-User for consent before returning information to the Client.
     * @since 1.0.0
     */
    public const REQUEST_PARAMETER_PROMPT_CONSENT = 'consent';
    /**
     * Prompt option: The Authorization Server SHOULD prompt the End-User to select a user account.
     * @since 1.0.0
     */
    public const REQUEST_PARAMETER_PROMPT_SELECT_ACCOUNT = 'select_account';

    /**
     * Supported Oauth 2 grant types for OpenID Connect.
     * @since 1.0.0
     */
    public const SUPPORTED_AUTHENTICATION_FLOWS = [
        Oauth2Module::GRANT_TYPE_IDENTIFIER_AUTH_CODE,
        Oauth2Module::GRANT_TYPE_IDENTIFIER_IMPLICIT,
    ];
}
