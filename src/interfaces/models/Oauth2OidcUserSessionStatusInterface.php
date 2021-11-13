<?php

namespace rhertogh\Yii2Oauth2Server\interfaces\models;

interface Oauth2OidcUserSessionStatusInterface
{
    /**
     * Allows the Oauth2 module to check if the user is online.
     * This is, for example, used in context of Refresh Tokens
     * when `openIdConnectIssueRefreshTokenWithoutOfflineAccessScope` is enabled.
     * @return bool Whether the user has an active session.
     * @since 1.0.0
     */
    public function hasActiveSession();
}
