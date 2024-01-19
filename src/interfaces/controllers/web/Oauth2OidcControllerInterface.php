<?php

namespace rhertogh\Yii2Oauth2Server\interfaces\controllers\web;

interface Oauth2OidcControllerInterface
{
    /**
     * The name for the controller in the module's controller map
     * @since 1.0.0
     */
    public const CONTROLLER_NAME = 'openid-connect';

    /**
     * Name for the Oauth2OidcUserinfoAction in the controller's actions
     * @since 1.0.0
     */
    public const ACTION_NAME_USERINFO = 'userinfo';

    /**
     * Name for the Oauth2OidcEndSessionAction in the controller's actions
     * @since 1.0.0
     */
    public const ACTION_END_SESSION = 'end-session';
}
