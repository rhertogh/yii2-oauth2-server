<?php

namespace rhertogh\Yii2Oauth2Server\interfaces\components\factories\grants;

use rhertogh\Yii2Oauth2Server\interfaces\components\server\grants\Oauth2ImplicitGrantInterface;

interface Oauth2ImplicitGrantFactoryInterface extends base\Oauth2GrantTypeFactoryInterface
{
    # region Oauth2GrantTypeFactoryInterface methods (overwritten for type covariance)
    /**
     * @inheritDoc
     * @return Oauth2ImplicitGrantInterface
     */
    public function getGrantType();
    # endregion
}
