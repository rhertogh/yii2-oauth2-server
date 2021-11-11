<?php

namespace rhertogh\Yii2Oauth2Server\interfaces\components\factories\grants;

use rhertogh\Yii2Oauth2Server\interfaces\components\server\grants\Oauth2PasswordGrantInterface;

interface Oauth2PasswordGrantFactoryInterface extends base\Oauth2GrantTypeFactoryInterface
{
    # region Oauth2GrantTypeFactoryInterface methods (overwritten for type covariance)
    /**
     * @inheritDoc
     * @return Oauth2PasswordGrantInterface
     */
    public function getGrantType();
    # endregion
}
