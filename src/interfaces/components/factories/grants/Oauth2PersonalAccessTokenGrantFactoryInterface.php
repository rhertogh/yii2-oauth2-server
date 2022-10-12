<?php

namespace rhertogh\Yii2Oauth2Server\interfaces\components\factories\grants;

use rhertogh\Yii2Oauth2Server\interfaces\components\server\grants\Oauth2PersonalAccessTokenGrantInterface;

interface Oauth2PersonalAccessTokenGrantFactoryInterface extends base\Oauth2GrantTypeFactoryInterface
{
    # region Oauth2GrantTypeFactoryInterface methods (overwritten for type covariance)
    /**
     * @inheritDoc
     * @return Oauth2PersonalAccessTokenGrantInterface
     */
    public function getGrantType();
    # endregion
}
