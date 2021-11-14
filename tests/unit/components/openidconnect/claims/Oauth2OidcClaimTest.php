<?php

namespace Yii2Oauth2ServerTests\unit\components\openidconnect\claims;

use rhertogh\Yii2Oauth2Server\components\openidconnect\claims\Oauth2OidcClaim;
use Yii2Oauth2ServerTests\unit\TestCase;

/**
 * @covers \rhertogh\Yii2Oauth2Server\components\openidconnect\claims\Oauth2OidcClaim
 */
class Oauth2OidcClaimTest extends TestCase
{
    public function testGetSetIdentifier()
    {
        $claim = new Oauth2OidcClaim();
        $identifier = 'test-claim';
        $this->assertEquals($claim, $claim->setIdentifier($identifier));
        $this->assertEquals($identifier, $claim->getIdentifier());
    }

    public function testGetIdentifierWithoutItBeingSet()
    {
        $claim = new Oauth2OidcClaim();

        $this->expectExceptionMessage('Trying to get claim identifier without it being set.');
        $claim->getIdentifier();
    }

    public function testGetSetDeterminer()
    {
        $claim = new Oauth2OidcClaim();
        $identifier = 'test-claim';
        $claim->setIdentifier($identifier);

        // string.
        $determiner = 'test-determiner';
        $this->assertEquals($claim, $claim->setDeterminer($determiner));
        $this->assertEquals($determiner, $claim->getDeterminer());

        // array.
        $determiner = ['test-determiner1', 'test-determiner2'];
        $this->assertEquals($claim, $claim->setDeterminer($determiner));
        $this->assertEquals($determiner, $claim->getDeterminer());

        // callable.
        $determiner = 'test-determiner';
        $this->assertEquals($claim, $claim->setDeterminer($determiner));
        $this->assertEquals($determiner, $claim->getDeterminer());

        // default to identifier if determiner is null.
        $this->assertEquals($claim, $claim->setDeterminer(null));
        $this->assertEquals($identifier, $claim->getDeterminer());
    }

    public function testGetSetDefaultValue()
    {
        $claim = new Oauth2OidcClaim();
        $defaultValue = 'test-default-value';
        $this->assertEquals($claim, $claim->setDefaultValue($defaultValue));
        $this->assertEquals($defaultValue, $claim->getDefaultValue());
    }
}
