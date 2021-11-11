<?php

namespace rhertogh\Yii2Oauth2Server\components\factories\grants;

use League\OAuth2\Server\Grant\PasswordGrant;
use rhertogh\Yii2Oauth2Server\interfaces\components\factories\grants\Oauth2PasswordGrantFactoryInterface;
use rhertogh\Yii2Oauth2Server\interfaces\components\server\grants\Oauth2PasswordGrantInterface;
use Yii;

class Oauth2PasswordGrantFactory extends base\Oauth2BaseGrantTypeFactory implements Oauth2PasswordGrantFactoryInterface
{
    /**
     * Time To Live for the authorization code, default value: 1 month.
     * @var string
     */
    public $refreshTokenTTL = 'P1M';

    /**
     * @inheritDoc
     */
    public function getGrantType()
    {
        /** @var Oauth2PasswordGrantInterface $passwordGrant */
        $passwordGrant = Yii::createObject(Oauth2PasswordGrantInterface::class, [
            $this->module->getUserRepository(),
            $this->module->getRefreshTokenRepository(),
        ]);

        $passwordGrant->setRefreshTokenTTL(new \DateInterval($this->refreshTokenTTL));

        return $passwordGrant;
    }
}
