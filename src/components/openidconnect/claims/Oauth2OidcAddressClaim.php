<?php

namespace rhertogh\Yii2Oauth2Server\components\openidconnect\claims;

use rhertogh\Yii2Oauth2Server\interfaces\components\openidconnect\claims\Oauth2OidcAddressClaimInterface;
use yii\base\Arrayable;
use yii\base\BaseObject;

/**
 * @see https://openid.net/specs/openid-connect-core-1_0.html#AddressClaim
 *
 * @property string $formatted Full mailing address, formatted for display or use on a mailing label.
 * This field MAY contain multiple lines, separated by newlines. Newlines can be represented either as a carriage
 * return/line feed pair ("\r\n") or as a single line feed character ("\n").
 *
 * @property string $streetAddress Full street address component, which MAY include house number, street name,
 * Post Office Box, and multi-line extended street address information. This field MAY contain multiple lines,
 * separated by newlines. Newlines can be represented either as a carriage return/line feed pair ("\r\n") or
 * as a single line feed character ("\n").
 *
 * @property string $locality City or locality component.
 *
 * @property string $region State, province, prefecture, or region component.
 *
 * @property string $postalCode Zip code or postal code component.
 *
 * @property string $country Country name component.
 */
class Oauth2OidcAddressClaim extends BaseObject implements Oauth2OidcAddressClaimInterface, \JsonSerializable
{
    /**
     * @var string
     */
    protected $_formatted;

    /**
     * @var string
     */
    protected $_street_address;

    /**
     * @var string
     */
    protected $_locality;

    /**
     * @var string
     */
    protected $_region;

    /**
     * @var string
     */
    protected $_postal_code;

    /**
     * @var string
     */
    protected $_country;

    /**
     * @inheritDoc
     */
    public function getFormatted()
    {
        return $this->_formatted;
    }

    /**
     * @inheritDoc
     */
    public function setFormatted($formatted)
    {
        $this->_formatted = $formatted;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getStreetAddress()
    {
        return $this->_street_address;
    }

    /**
     * @inheritDoc
     */
    public function setStreetAddress($street_address)
    {
        $this->_street_address = $street_address;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getLocality()
    {
        return $this->_locality;
    }

    /**
     * @inheritDoc
     */
    public function setLocality($locality)
    {
        $this->_locality = $locality;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getRegion()
    {
        return $this->_region;
    }

    /**
     * @inheritDoc
     */
    public function setRegion($region)
    {
        $this->_region = $region;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getPostalCode()
    {
        return $this->_postal_code;
    }

    /**
     * @inheritDoc
     */
    public function setPostalCode($postal_code)
    {
        $this->_postal_code = $postal_code;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getCountry()
    {
        return $this->_country;
    }

    /**
     * @inheritDoc
     */
    public function setCountry($country)
    {
        $this->_country = $country;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function jsonSerialize()
    {
        return [
            'formatted' => $this->getFormatted(),
            'streetAddress' => $this->getStreetAddress(),
            'locality' => $this->getLocality(),
            'region' => $this->getRegion(),
            'postalCode' => $this->getPostalCode(),
            'country' => $this->getCountry(),
        ];
    }
}
