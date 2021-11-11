<?php

namespace Yii2Oauth2ServerTests\unit\controllers\web\base;

use rhertogh\Yii2Oauth2Server\controllers\web\base\Oauth2BaseApiController;
use rhertogh\Yii2Oauth2Server\Oauth2Module;
use Yii2Oauth2ServerTests\unit\controllers\web\_base\Oauth2BaseWebControllerTest;

/**
 * @covers \rhertogh\Yii2Oauth2Server\controllers\web\base\Oauth2BaseApiController
 */
class Oauth2BaseApiControllerTest extends Oauth2BaseWebControllerTest
{
    /**
     * @inheritDoc
     */
    protected function getMockController()
    {
        $this->mockWebApplication();
        return new class('base', Oauth2Module::getInstance()) extends Oauth2BaseApiController {};
    }

    /**
     * @inheritDoc
     */
    protected function getExpectedActions()
    {
        return [];
    }

    public function testCorsBehavior()
    {
        $this->assertTrue($this->hasCorsBehavior(), 'Oauth2CertificatesController should have a CORS filter.');
    }
}
