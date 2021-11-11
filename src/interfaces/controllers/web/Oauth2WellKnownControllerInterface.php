<?php

namespace rhertogh\Yii2Oauth2Server\interfaces\controllers\web;

interface Oauth2WellKnownControllerInterface
{
    /**
     * The name for the controller in the module's controller map
     * @since 1.0.0
     */
    public const CONTROLLER_NAME = 'well-known';

    /**
     * Name for the Oauth2OpenidConfigurationAction in the controller's actions
     * @since 1.0.0
     */
    public const ACTION_NAME_OPENID_CONFIGURATION = 'openid-configuration';
}
