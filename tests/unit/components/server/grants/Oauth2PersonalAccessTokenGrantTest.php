<?php

namespace Yii2Oauth2ServerTests\unit\components\server\grants;

use rhertogh\Yii2Oauth2Server\components\factories\grants\Oauth2PersonalAccessTokenGrantFactory;
use Yii2Oauth2ServerTests\unit\components\server\grants\_base\BaseOauth2GrantTest;

/**
 * @covers \rhertogh\Yii2Oauth2Server\components\server\grants\Oauth2PersonalAccessTokenGrant
 * @covers \rhertogh\Yii2Oauth2Server\components\server\grants\traits\Oauth2GrantTrait
 */
class Oauth2PersonalAccessTokenGrantTest extends BaseOauth2GrantTest
{
    protected function getMockGrant($module)
    {
        return (new Oauth2PersonalAccessTokenGrantFactory(['module' => $module]))->getGrantType();
    }
}
