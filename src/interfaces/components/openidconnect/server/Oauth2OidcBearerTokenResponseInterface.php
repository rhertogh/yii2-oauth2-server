<?php

namespace rhertogh\Yii2Oauth2Server\interfaces\components\openidconnect\server;

use League\OAuth2\Server\ResponseTypes\ResponseTypeInterface;
use rhertogh\Yii2Oauth2Server\Oauth2Module;

interface Oauth2OidcBearerTokenResponseInterface extends ResponseTypeInterface
{
    /**
     * The OpenID Connect ID Token value associated with the authenticated session.
     * @see https://openid.net/specs/openid-connect-core-1_0.html#rfc.section.3.1.3.3
     * @since 1.0.0
     */
    public const TOKEN_RESPONSE_ID_TOKEN = 'id_token';

    /**
     * @param Oauth2Module $module
     * @since 1.0.0
     */
    public function __construct(Oauth2Module $module);

    /**
     * Get the module for this response.
     * @return Oauth2Module
     * @since 1.0.0
     */
    public function getModule();
}
