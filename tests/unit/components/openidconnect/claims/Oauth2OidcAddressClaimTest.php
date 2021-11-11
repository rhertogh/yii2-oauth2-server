<?php

namespace Yii2Oauth2ServerTests\unit\components\openidconnect\claims;

use rhertogh\Yii2Oauth2Server\components\openidconnect\claims\Oauth2OidcAddressClaim;
use Yii2Oauth2ServerTests\unit\TestCase;

/**
 * @covers \rhertogh\Yii2Oauth2Server\components\openidconnect\claims\Oauth2OidcAddressClaim
 */
class Oauth2OidcAddressClaimTest extends TestCase
{
    public function testGettersSetters()
    {
        $formatted = 'test formatted';
        $streetAddress = 'test streetAddress';
        $locality = 'test locality';
        $region = 'test region';
        $postalCode = 'test postalCode';
        $country = 'test country';

        $addressClaim = new Oauth2OidcAddressClaim();
        $addressClaim->setFormatted($formatted);
        $addressClaim->setStreetAddress($streetAddress);
        $addressClaim->setLocality($locality);
        $addressClaim->setRegion($region);
        $addressClaim->setPostalCode($postalCode);
        $addressClaim->setCountry($country);

        $this->assertEquals($formatted, $addressClaim->getFormatted());
        $this->assertEquals($streetAddress, $addressClaim->getStreetAddress());
        $this->assertEquals($locality, $addressClaim->getLocality());
        $this->assertEquals($region, $addressClaim->getRegion());
        $this->assertEquals($postalCode, $addressClaim->getPostalCode());
        $this->assertEquals($country, $addressClaim->getCountry());
    }

    public function testJsonSerialize()
    {
        $data = [
            'formatted' => 'test formatted',
            'streetAddress' => 'test streetAddress',
            'locality' => 'test locality',
            'region' => 'test region',
            'postalCode' => 'test postalCode',
            'country' => 'test country',
        ];

        $addressClaim = new Oauth2OidcAddressClaim($data);

        $this->assertEquals(json_encode($data), json_encode($addressClaim));
    }
}
