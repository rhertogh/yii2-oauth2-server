<?php

namespace Yii2Oauth2ServerTests\functional;

use Codeception\Util\HttpCode;
use Yii2Oauth2ServerTests\ApiTester;
use Yii2Oauth2ServerTests\functional\_base\BaseGrantCest;

/**
 * Ensure we can't access the test API without authorization
 */
class WellKnownCest extends BaseGrantCest
{
    public function oauth2ConfigurationTest(ApiTester $I)
    {
        $I->sendGet('.well-known/openid-configuration');
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeHttpHeader('Content-Type', 'application/json; charset=UTF-8');
    }
}
