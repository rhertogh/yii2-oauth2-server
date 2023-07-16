<?php

namespace rhertogh\Yii2Oauth2Server\components\factories\grants;

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
        $accessTokenTtl = $this->getDefaultAccessTokenTTL()
            ?? $this->module->getDefaultAccessTokenTTL()
            ?? new \DateInterval('PT1H');

        return Yii::createObject(
            [
                'class' => Oauth2ImplicitGrantInterface::class,
                'module' => $this->module,
            ],
            [
                $accessTokenTtl
            ]
        );
    }
}
