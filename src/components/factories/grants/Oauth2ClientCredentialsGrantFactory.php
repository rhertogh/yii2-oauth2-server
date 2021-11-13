<?php

namespace rhertogh\Yii2Oauth2Server\components\factories\grants;

use League\OAuth2\Server\Grant\AuthCodeGrant;
use League\OAuth2\Server\Grant\ClientCredentialsGrant;
use rhertogh\Yii2Oauth2Server\components\server\grants\Oauth2ClientCredentialsGrant;
use rhertogh\Yii2Oauth2Server\interfaces\components\factories\grants\Oauth2AuthCodeGrantFactoryInterface;
use rhertogh\Yii2Oauth2Server\interfaces\components\factories\grants\Oauth2ClientCredentialsGrantFactoryInterface;
use rhertogh\Yii2Oauth2Server\interfaces\components\repositories\Oauth2AuthCodeRepositoryInterface;
use rhertogh\Yii2Oauth2Server\interfaces\components\repositories\Oauth2RefreshTokenRepositoryInterface;
use rhertogh\Yii2Oauth2Server\interfaces\components\server\grants\Oauth2ClientCredentialsGrantInterface;
use Yii;

class Oauth2ClientCredentialsGrantFactory extends base\Oauth2BaseGrantTypeFactory implements
    Oauth2ClientCredentialsGrantFactoryInterface
{
    /**
     * @inheritDoc
     * @return Oauth2ClientCredentialsGrantInterface
     */
    public function getGrantType()
    {
        return Yii::createObject([
            'class' => Oauth2ClientCredentialsGrantInterface::class,
            'module' => $this->module,
        ]);
    }
}
