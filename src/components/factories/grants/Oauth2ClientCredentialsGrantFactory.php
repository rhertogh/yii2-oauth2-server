<?php

namespace rhertogh\Yii2Oauth2Server\components\factories\grants;

use rhertogh\Yii2Oauth2Server\interfaces\components\factories\grants\Oauth2ClientCredentialsGrantFactoryInterface;
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
