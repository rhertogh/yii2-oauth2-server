<?php

namespace rhertogh\Yii2Oauth2Server\interfaces\controllers\web;

interface Oauth2CertificatesControllerInterface
{
    /**
     * The name for the controller in the module's controller map
     * @since 1.0.0
     */
    public const CONTROLLER_NAME = 'certificates';

    /**
     * Name for the Oauth2JwksAction in the controller's actions
     * @since 1.0.0
     */
    public const ACTION_NAME_JWKS = 'jwks';
}
