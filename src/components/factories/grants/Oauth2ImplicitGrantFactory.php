<?php

namespace rhertogh\Yii2Oauth2Server\components\factories\grants;

use League\OAuth2\Server\Grant\ImplicitGrant;
use rhertogh\Yii2Oauth2Server\interfaces\components\factories\grants\Oauth2ImplicitGrantFactoryInterface;
use rhertogh\Yii2Oauth2Server\interfaces\components\server\grants\Oauth2ImplicitGrantInterface;
use Yii;

class Oauth2ImplicitGrantFactory extends base\Oauth2BaseGrantTypeFactory implements Oauth2ImplicitGrantFactoryInterface
{
    /**
     * @inheritDoc
     * @return Oauth2ImplicitGrantInterface
     */
    public function getGrantType()
    {
        return Yii::createObject(Oauth2ImplicitGrantInterface::class, [
            new \DateInterval($this->accessTokenTTL ?? $this->module->defaultAccessTokenTTL ?? 'PT1H')
        ]);
    }
}
