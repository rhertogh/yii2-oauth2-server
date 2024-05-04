<?php

namespace rhertogh\Yii2Oauth2Server\interfaces\components\openidconnect\server\responses;

use rhertogh\Yii2Oauth2Server\interfaces\components\server\responses\Oauth2BearerTokenResponseInterface;

interface Oauth2OidcBearerTokenResponseInterface extends Oauth2BearerTokenResponseInterface
{
    /**
     * The OpenID Connect ID Token value associated with the authenticated session.
     * @see https://openid.net/specs/openid-connect-core-1_0.html#rfc.section.3.1.3.3
     * @since 1.0.0
     */
    public const TOKEN_RESPONSE_ID_TOKEN = 'id_token';
}
