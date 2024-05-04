<?php

namespace rhertogh\Yii2Oauth2Server\interfaces\controllers\web;

interface Oauth2ServerControllerInterface
{
    /**
     * The name for the controller in the module's controller map
     * @since 1.0.0
     */
    public const CONTROLLER_NAME = 'server';

    /**
     * Name for the Oauth2AccessTokenAction in the controller's actions
     * @since 1.0.0
     */
    public const ACTION_NAME_ACCESS_TOKEN = 'access-token';
    /**
     * Name for the Oauth2AuthorizeAction in the controller's actions
     * @since 1.0.0
     */
    public const ACTION_NAME_AUTHORIZE = 'authorize';

    public const ACTION_NAME_REVOKE = 'revoke';
}
