<?php

namespace rhertogh\Yii2Oauth2Server\components\factories\grants;

use rhertogh\Yii2Oauth2Server\interfaces\components\factories\grants\Oauth2PersonalAccessTokenGrantFactoryInterface;
use rhertogh\Yii2Oauth2Server\interfaces\components\server\grants\Oauth2PersonalAccessTokenGrantInterface;
use Yii;

class Oauth2PersonalAccessTokenGrantFactory extends base\Oauth2BaseGrantTypeFactory implements Oauth2PersonalAccessTokenGrantFactoryInterface
{
    /**
     * Time To Live for the access token, default value: 1 year.
     * The format should be a DateInterval duration (https://www.php.net/manual/en/dateinterval.construct.php).
     * @var string
     */
    public $accessTokenTTL = 'P1Y';

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
                new \DateInterval($this->accessTokenTTL)
            ]
        );

        return $patGrant;
    }
}
