<?php

namespace rhertogh\Yii2Oauth2Server\components\factories\grants;

use rhertogh\Yii2Oauth2Server\interfaces\components\factories\grants\Oauth2AuthCodeGrantFactoryInterface;
use rhertogh\Yii2Oauth2Server\interfaces\components\server\grants\Oauth2AuthCodeGrantInterface;
use Yii;

class Oauth2AuthCodeGrantFactory extends base\Oauth2BaseGrantTypeFactory implements Oauth2AuthCodeGrantFactoryInterface
{
    /**
     * Time To Live for the authorization code, default value: 10 minutes.
     * @var string
     */
    public $authCodeTTL = 'PT10M';

    /**
     * Time To Live for the refreshToken, default value: 1 month.
     * @var string
     */
    public $refreshTokenTTL = 'P1M';

    /**
     * @inheritDoc
     */
    public function getGrantType()
    {
        /** @var Oauth2AuthCodeGrantInterface $authCodeGrant */
        $authCodeGrant = Yii::createObject(
            [
                'class' => Oauth2AuthCodeGrantInterface::class,
                'module' => $this->module,
            ],
            [
                $this->module->getAuthCodeRepository(),
                $this->module->getRefreshTokenRepository(),
                new \DateInterval($this->authCodeTTL)
            ]
        );

        $authCodeGrant->setRefreshTokenTTL(new \DateInterval($this->refreshTokenTTL));

        return $authCodeGrant;
    }
}
