<?php

namespace Yii2Oauth2ServerTests\unit\traits;

use rhertogh\Yii2Oauth2Server\components\openidconnect\server\Oauth2OidcBearerTokenResponse;
use rhertogh\Yii2Oauth2Server\components\openidconnect\claims\Oauth2OidcClaim;
use rhertogh\Yii2Oauth2Server\interfaces\components\openidconnect\server\Oauth2OidcBearerTokenResponseInterface;
use rhertogh\Yii2Oauth2Server\interfaces\components\openidconnect\scope\Oauth2OidcClaimInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\Oauth2OidcUserInterface;
use rhertogh\Yii2Oauth2Server\Oauth2Module;
use rhertogh\Yii2Oauth2Server\traits\models\Oauth2OidcUserIdentityTrait;
use yii\base\Component;
use Yii2Oauth2ServerTests\_helpers\TestUserModelOidc;
use Yii2Oauth2ServerTests\unit\TestCase;

/**
 * @covers \rhertogh\Yii2Oauth2Server\traits\models\Oauth2OidcUserIdentityTrait
 */
class Oauth2OidcUserIdentityTraitTest extends TestCase
{
    public function testGetOpenIdConnectClaimValue()
    {
        $this->mockWebApplication();
        $response = new Oauth2OidcBearerTokenResponse(Oauth2Module::getInstance());

        $identity = new class extends TestUserModelOidc {

            public $email = 'test@test.test';

            public function getEmailAddress()
            {
                return $this->email;
            }

            public function customFunction()
            {
                return 'custom-function-value';
            }

            public function getNested1()
            {
                return new class extends Component {
                    public function getNested2()
                    {
                        return 'nested-value';
                    }
                };
            }
        };

        // object method.
        $claim = new Oauth2OidcClaim(['determiner' => 'customFunction']);
        $this->assertEquals('custom-function-value', $identity->getOpenIdConnectClaimValue($claim, $response));

        // object property.
        $claim = new Oauth2OidcClaim(['determiner' => 'email']);
        $this->assertEquals('test@test.test', $identity->getOpenIdConnectClaimValue($claim, $response));

        // object nested property.
        $claim = new Oauth2OidcClaim(['determiner' => 'nested1.nested2']);
        $this->assertEquals('nested-value', $identity->getOpenIdConnectClaimValue($claim, $response));

        // virtual property via getter function, not defined by trait but by component,
        // however we should still validate it works (both via getter and direct function call).
        $claim = new Oauth2OidcClaim(['determiner' => 'emailAddress']);
        $this->assertEquals('test@test.test', $identity->getOpenIdConnectClaimValue($claim, $response));
        $claim = new Oauth2OidcClaim(['determiner' => 'getEmailAddress']);
        $this->assertEquals('test@test.test', $identity->getOpenIdConnectClaimValue($claim, $response));

        // default value.
        $claim = new Oauth2OidcClaim(['determiner' => 'non-existing']);
        $this->assertNull($identity->getOpenIdConnectClaimValue($claim, $response));
        $claim = new Oauth2OidcClaim(['determiner' => 'non-existing', 'defaultValue' => 'test-default-value']);
        $this->assertEquals('test-default-value', $identity->getOpenIdConnectClaimValue($claim, $response));
    }

    public function testGetOpenIdConnectClaimValueCallable()
    {
        $this->mockWebApplication();
        $identity = new TestUserModelOidc([
            'id' => 123,
        ]);
        $response = new Oauth2OidcBearerTokenResponse(Oauth2Module::getInstance());

        $callableTest = $this->getMockBuilder(\stdClass::class)
            ->addMethods(['testFunction'])
            ->getMock();

        $callableTest->expects($this->once())
            ->method('testFunction')
            ->with(
                $this->callback(function ($user) {
                    return $user instanceof Oauth2OidcUserInterface
                        && $user->getIdentifier() == 123;
                }),
                $this->callback(function ($claim) {
                    return $claim instanceof Oauth2OidcClaimInterface
                        && $claim->getIdentifier() == 'test-callable-claim';
                }),
                $this->callback(function ($response) {
                    return $response instanceof Oauth2OidcBearerTokenResponseInterface
                        && $response->getModule() === Oauth2Module::getInstance();
                }),
            )
            ->willReturn('custom-callable-value');

        $claim = new Oauth2OidcClaim([
            'identifier' => 'test-callable-claim',
            'determiner' => [$callableTest, 'testFunction']
        ]);
        $this->assertEquals('custom-callable-value', $identity->getOpenIdConnectClaimValue($claim, $response));
    }

    public function testGetOpenIdConnectClaimValueInvalidDeterminer()
    {
        $this->mockConsoleApplication();
        $identity = new class extends Component {
            use Oauth2OidcUserIdentityTrait;
        };
        $response = new Oauth2OidcBearerTokenResponse(Oauth2Module::getInstance());

        $claim = new Oauth2OidcClaim([
            'identifier' => 'test-callable-claim',
            'determiner' => new \stdClass(),
        ]);
        $this->expectExceptionMessage('Invalid determiner "stdClass" for claim "test-callable-claim".');
        $identity->getOpenIdConnectClaimValue($claim, $response);
    }
}
