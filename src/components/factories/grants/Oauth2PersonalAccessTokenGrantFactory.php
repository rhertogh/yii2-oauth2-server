<?php

namespace rhertogh\Yii2Oauth2Server\components\factories\grants;

use rhertogh\Yii2Oauth2Server\interfaces\components\factories\grants\Oauth2PersonalAccessTokenGrantFactoryInterface;
use rhertogh\Yii2Oauth2Server\interfaces\components\server\grants\Oauth2PersonalAccessTokenGrantInterface;
use Yii;

class Oauth2PersonalAccessTokenGrantFactory extends base\Oauth2BaseGrantTypeFactory implements Oauth2PersonalAccessTokenGrantFactoryInterface
{
    /**
     * Default TTL for Personal Access Tokens is 1 year.
     * @inheritdoc
     */
    protected $_accessTokenTTL = 'P1Y';

    /**
     * @inheritDoc
     */
    public function getGrantType()
    {
        /** @var Oauth2PersonalAccessTokenGrantInterface $patGrant */
        $patGrant = Yii::createObject(
            [
                'class' => Oauth2PersonalAccessTokenGrantInterface::class,
                'module' => $this->module,
            ],
            [
                $this->module->getUserRepository(),
                $this->module->getAccessTokenRepository(),
            ]
        );

        return $patGrant;
    }
}
