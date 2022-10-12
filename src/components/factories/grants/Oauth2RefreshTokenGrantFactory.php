<?php

namespace rhertogh\Yii2Oauth2Server\components\factories\grants;

use rhertogh\Yii2Oauth2Server\interfaces\components\factories\grants\Oauth2RefreshTokenGrantFactoryInterface;
use rhertogh\Yii2Oauth2Server\interfaces\components\server\grants\Oauth2RefreshTokenGrantInterface;
use Yii;

class Oauth2RefreshTokenGrantFactory extends base\Oauth2BaseGrantTypeFactory implements
    Oauth2RefreshTokenGrantFactoryInterface
{
    /**
     * @var string Time To Live for the refresh token, default value: 1 month.
     */
    public $refreshTokenTTL = 'P1M';

    /**
     * @inheritDoc
     */
    public function getGrantType()
    {
        /** @var Oauth2RefreshTokenGrantInterface $refreshTokenGrant */
        $refreshTokenGrant = Yii::createObject(
            [
                'class' => Oauth2RefreshTokenGrantInterface::class,
                'module' => $this->module,
            ],
            [
                $this->module->getRefreshTokenRepository(),
            ]
        );

        $refreshTokenGrant->setRefreshTokenTTL(new \DateInterval($this->refreshTokenTTL));

        return $refreshTokenGrant;
    }
}
