<?php

namespace Yii2Oauth2ServerTests\unit\components\server\grants;

use rhertogh\Yii2Oauth2Server\components\factories\grants\Oauth2PasswordGrantFactory;
use Yii2Oauth2ServerTests\unit\components\server\grants\_base\BaseOauth2GrantTest;

/**
 * @covers \rhertogh\Yii2Oauth2Server\components\server\grants\Oauth2PasswordGrant
 */
class Oauth2PasswordGrantTest extends BaseOauth2GrantTest
{
    protected function getMockGrant($module)
    {
        return (new Oauth2PasswordGrantFactory(['module' => $module]))->getGrantType();
    }
}
