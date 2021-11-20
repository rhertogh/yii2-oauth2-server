<?php

namespace rhertogh\Yii2Oauth2Server\interfaces\components\user;

use rhertogh\Yii2Oauth2Server\components\server\grants\Oauth2PasswordGrant;
use rhertogh\Yii2Oauth2Server\interfaces\components\server\grants\Oauth2PasswordGrantInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\Oauth2ClientInterface;
use yii\web\IdentityInterface;

interface Oauth2PasswordGrantUserComponentInterface
{
    /**
     * This method is called before logging in a user via the Oauth2 Password Grant.
     * @param IdentityInterface $identity the user identity information
     * @param Oauth2ClientInterface $client The client
     * @param Oauth2PasswordGrantInterface $grant The password grant
     * @return bool whether the user should continue to be logged in
     */
    public function beforeOauth2PasswordGrantLogin($identity, $client, $grant);

    /**
     * This method is called after the user is successfully logged in via the Oauth2 Password Grant.
     * @param IdentityInterface $identity the user identity information
     * @param Oauth2PasswordGrant $grant The password grant
     */
    public function afterOauth2PasswordGrantLogin($identity, $grant);
}
