<?php

namespace rhertogh\Yii2Oauth2Server\interfaces\models\external\user;

use rhertogh\Yii2Oauth2Server\interfaces\components\openidconnect\scope\Oauth2OidcClaimInterface;
use rhertogh\Yii2Oauth2Server\Oauth2Module;

interface Oauth2OidcUserInterface extends Oauth2UserInterface, Oauth2UserAuthenticatedAtInterface
{
    /**
     * Get the value for a claim.
     * @param Oauth2OidcClaimInterface $claim
     * @param Oauth2Module $module
     * @return mixed
     * @since 1.0.0
     */
    public function getOpenIdConnectClaimValue($claim, $module);
}
