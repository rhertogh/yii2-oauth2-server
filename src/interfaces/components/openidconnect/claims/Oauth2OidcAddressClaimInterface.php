<?php

namespace rhertogh\Yii2Oauth2Server\interfaces\components\openidconnect\claims;


/**
 * @see https://openid.net/specs/openid-connect-core-1_0.html#AddressClaim
 */
interface Oauth2OidcAddressClaimInterface
{
    /**
     * Full mailing address, formatted for display or use on a mailing label.
     * This field MAY contain multiple lines, separated by newlines. Newlines can be represented either as
     * a carriage return/line feed pair ("\r\n") or as a single line feed character ("\n").
     * @return string
     *@since 1.0.0
     */
    public function getFormatted();

    /**
     * Full mailing address, formatted for display or use on a mailing label.
     * This field MAY contain multiple lines, separated by newlines. Newlines can be represented either as
     * a carriage return/line feed pair ("\r\n") or as a single line feed character ("\n").
     * @param string $formatted
     * @return $this
     * @since 1.0.0
     */
    public function setFormatted($formatted);

    /**
     * Full street address component, which MAY include house number, street name, Post Office Box,
     * and multi-line extended street address information. This field MAY contain multiple lines, separated by newlines.
     * Newlines can be represented either as a carriage return/line feed pair ("\r\n")
     * or as a single line feed character ("\n").
     * @return string
     * @since 1.0.0
     */
    public function getStreetAddress();

    /**
     * Full street address component, which MAY include house number, street name, Post Office Box,
     * and multi-line extended street address information. This field MAY contain multiple lines, separated by newlines.
     * Newlines can be represented either as a carriage return/line feed pair ("\r\n")
     * or as a single line feed character ("\n").
     * @param string $street_address
     * @return $this
     * @since 1.0.0
     */
    public function setStreetAddress($street_address);

    /**
     * City or locality component.
     * @return string
     * @since 1.0.0
     */
    public function getLocality();

    /**
     * City or locality component.
     * @param string $locality
     * @return $this
     * @since 1.0.0
     */
    public function setLocality($locality);

    /**
     * State, province, prefecture, or region component.
     * @return string
     * @since 1.0.0
     */
    public function getRegion();

    /**
     * State, province, prefecture, or region component.
     * @param string $region
     * @return $this
     * @since 1.0.0
     */
    public function setRegion($region);

    /**
     * Zip code or postal code component.
     * @return string
     * @since 1.0.0
     */
    public function getPostalCode();

    /**
     * Zip code or postal code component.
     * @param string $postal_code
     * @return $this
     * @since 1.0.0
     */
    public function setPostalCode($postal_code);

    /**
     * Country name component.
     * @return string
     * @since 1.0.0
     */
    public function getCountry();

    /**
     * Country name component.
     * @param string $country
     * @return $this
     * @since 1.0.0
     */
    public function setCountry($country);
}
