<?php

namespace rhertogh\Yii2Oauth2Server\components\factories\grants;

use League\OAuth2\Server\Grant\AuthCodeGrant;
use League\OAuth2\Server\Grant\RefreshTokenGrant;
use rhertogh\Yii2Oauth2Server\components\server\grants\Oauth2RefreshTokenGrant;
use rhertogh\Yii2Oauth2Server\interfaces\components\factories\grants\Oauth2AuthCodeGrantFactoryInterface;
use rhertogh\Yii2Oauth2Server\interfaces\components\factories\grants\Oauth2RefreshTokenGrantFactoryInterface;
use rhertogh\Yii2Oauth2Server\interfaces\components\repositories\Oauth2AuthCodeRepositoryInterface;
use rhertogh\Yii2Oauth2Server\interfaces\components\repositories\Oauth2RefreshTokenRepositoryInterface;
use rhertogh\Yii2Oauth2Server\interfaces\components\server\grants\Oauth2RefreshTokenGrantInterface;
use Yii;

class Oauth2RefreshTokenGrantFactory extends base\Oauth2BaseGrantTypeFactory implements Oauth2RefreshTokenGrantFactoryInterface
{
    /**
     * @var string Time To Live for the authorization code, default value: 1 month.
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
