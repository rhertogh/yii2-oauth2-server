<?php

namespace rhertogh\Yii2Oauth2Server\interfaces\controllers\web;

interface Oauth2ConsentControllerInterface
{
    /**
     * The name for the controller in the module's controller map
     * @since 1.0.0
     */
    public const CONTROLLER_NAME = 'consent';

    /**
     * Name for the Oauth2AuthorizeClientAction in the controller's actions
     * @since 1.0.0
     */
    public const ACTION_NAME_AUTHORIZE_CLIENT = 'authorize-client';
}
