<?php

namespace rhertogh\Yii2Oauth2Server\interfaces\components\openidconnect\scope;

use rhertogh\Yii2Oauth2Server\interfaces\models\Oauth2OidcUserInterface;
use yii\base\Configurable;

interface Oauth2OidcClaimInterface extends Configurable
{
    /**
     * Subject - Identifier for the End-User at the Issuer.
     * @see https://openid.net/specs/openid-connect-core-1_0.html#StandardClaims
     * @since 1.0.0
     */
    public const OPENID_CONNECT_CLAIM_SUB = 'sub';

    /**
     * Time when the End-User authentication occurred
     * @see https://openid.net/specs/openid-connect-core-1_0.html#IDToken
     * @since 1.0.0
     */
    public const OPENID_CONNECT_CLAIM_AUTH_TIME = 'auth_time';
    /**
     * String value used to associate a Client session with an ID Token, and to mitigate replay attacks.
     * The value is passed through unmodified from the Authentication Request to the ID Token.
     * @see https://openid.net/specs/openid-connect-core-1_0.html#IDToken
     * @since 1.0.0
     */
    public const OPENID_CONNECT_CLAIM_NONCE = 'nonce';

    /**
     * End-User's full name in displayable form including all name parts, possibly including titles and suffixes,
     * ordered according to the End-User's locale and preferences.
     * @see https://openid.net/specs/openid-connect-core-1_0.html#StandardClaims
     * @since 1.0.0
     */
    public const OPENID_CONNECT_CLAIM_NAME = 'name';
    /**
     * Surname(s) or last name(s) of the End-User. Note that in some cultures, people can have multiple family names or
     * no family name; all can be present, with the names being separated by space characters.
     * @see https://openid.net/specs/openid-connect-core-1_0.html#StandardClaims
     * @since 1.0.0
     */
    public const OPENID_CONNECT_CLAIM_FAMILY_NAME = 'family_name';
    /**
     * Given name(s) or first name(s) of the End-User. Note that in some cultures, people can have multiple given names;
     * all can be present, with the names being separated by space characters.
     * @see https://openid.net/specs/openid-connect-core-1_0.html#StandardClaims
     * @since 1.0.0
     */
    public const OPENID_CONNECT_CLAIM_GIVEN_NAME = 'given_name';
    /**
     * Middle name(s) of the End-User. Note that in some cultures, people can have multiple middle names;
     * all can be present, with the names being separated by space characters. Also note that in some cultures,
     * middle names are not used.
     * @see https://openid.net/specs/openid-connect-core-1_0.html#StandardClaims
     * @since 1.0.0
     */
    public const OPENID_CONNECT_CLAIM_MIDDLE_NAME = 'middle_name';
    /**
     * Casual name of the End-User that may or may not be the same as the given_name. For instance, a nickname value of
     * Mike might be returned alongside a given_name value of Michael.
     * @see https://openid.net/specs/openid-connect-core-1_0.html#StandardClaims
     * @since 1.0.0
     */
    public const OPENID_CONNECT_CLAIM_NICKNAME = 'nickname';
    /**
     * Shorthand name by which the End-User wishes to be referred to at the RP, such as janedoe or j.doe.
     * This value MAY be any valid JSON string including special characters such as @, /, or whitespace.
     * @see https://openid.net/specs/openid-connect-core-1_0.html#StandardClaims
     * @since 1.0.0
     */
    public const OPENID_CONNECT_CLAIM_PREFERRED_USERNAME = 'preferred_username';
    /**
     * URL of the End-User's profile page. The contents of this Web page SHOULD be about the End-User.
     * @see https://openid.net/specs/openid-connect-core-1_0.html#StandardClaims
     * @since 1.0.0
     */
    public const OPENID_CONNECT_CLAIM_PROFILE = 'profile';
    /**
     * URL of the End-User's profile picture. This URL MUST refer to an image file
     * (for example, a PNG, JPEG, or GIF image file), rather than to a Web page containing an image.
     * Note that this URL SHOULD specifically reference a profile photo of the End-User suitable for displaying when
     * describing the End-User, rather than an arbitrary photo taken by the End-User.
     * @see https://openid.net/specs/openid-connect-core-1_0.html#StandardClaims
     * @since 1.0.0
     */
    public const OPENID_CONNECT_CLAIM_PICTURE = 'picture';
    /**
     * URL of the End-User's Web page or blog. This Web page SHOULD contain information published by the End-User or an
     * organization that the End-User is affiliated with.
     * @see https://openid.net/specs/openid-connect-core-1_0.html#StandardClaims
     * @since 1.0.0
     */
    public const OPENID_CONNECT_CLAIM_WEBSITE = 'website';
    /**
     * End-User's gender. Values defined by this specification are female and male.
     * Other values MAY be used when neither of the defined values are applicable.
     * @see https://openid.net/specs/openid-connect-core-1_0.html#StandardClaims
     * @since 1.0.0
     */
    public const OPENID_CONNECT_CLAIM_GENDER = 'gender';
    /**
     * End-User's birthday, represented as an ISO 8601:2004 [ISO8601‑2004] YYYY-MM-DD format.
     * The year MAY be 0000, indicating that it is omitted. To represent only the year, YYYY format is allowed.
     * @see https://openid.net/specs/openid-connect-core-1_0.html#StandardClaims
     * @since 1.0.0
     */
    public const OPENID_CONNECT_CLAIM_BIRTHDATE = 'birthdate';
    /**
     * String from zoneinfo [zoneinfo] time zone database representing the End-User's time zone.
     * For example, Europe/Paris or America/Los_Angeles.
     * @see https://openid.net/specs/openid-connect-core-1_0.html#StandardClaims
     * @since 1.0.0
     */
    public const OPENID_CONNECT_CLAIM_ZONEINFO = 'zoneinfo';
    /**
     * End-User's locale, represented as a BCP47 [RFC5646] language tag.
     * This is typically an ISO 639-1 Alpha-2 [ISO639‑1] language code in lowercase
     * and an ISO 3166-1 Alpha-2 [ISO3166‑1] country code in uppercase, separated by a dash.
     * For example, en-US or fr-CA.
     * @see https://openid.net/specs/openid-connect-core-1_0.html#StandardClaims
     * @since 1.0.0
     */
    public const OPENID_CONNECT_CLAIM_LOCALE = 'locale';
    /**
     * Time the End-User's information was last updated.
     * Its value is a JSON number representing the number of seconds from 1970-01-01T0:0:0Z
     * as measured in UTC until the date/time.
     * @see https://openid.net/specs/openid-connect-core-1_0.html#StandardClaims
     * @since 1.0.0
     */
    public const OPENID_CONNECT_CLAIM_UPDATED_AT = 'updated_at';
    /**
     * End-User's preferred e-mail address.
     * @see https://openid.net/specs/openid-connect-core-1_0.html#StandardClaims
     * @since 1.0.0
     */
    public const OPENID_CONNECT_CLAIM_EMAIL = 'email';
    /**
     * True if the End-User's e-mail address has been verified; otherwise false.
     * @see https://openid.net/specs/openid-connect-core-1_0.html#StandardClaims
     * @since 1.0.0
     */
    public const OPENID_CONNECT_CLAIM_EMAIL_VERIFIED = 'email_verified';
    /**
     * End-User's preferred telephone number. E.164 [E.164] is RECOMMENDED as the format of this Claim,
     * for example, +1 (425) 555-1212 or +56 (2) 687 2400. If the phone number contains an extension,
     * it is RECOMMENDED that the extension be represented using the RFC 3966 [RFC3966] extension syntax,
     * for example, +1 (604) 555-1234;ext=5678.
     * @see https://openid.net/specs/openid-connect-core-1_0.html#StandardClaims
     * @since 1.0.0
     */
    public const OPENID_CONNECT_CLAIM_PHONE_NUMBER = 'phone_number';
    /**
     * True if the End-User's phone number has been verified; otherwise false.
     * @see https://openid.net/specs/openid-connect-core-1_0.html#StandardClaims
     * @since 1.0.0
     */
    public const OPENID_CONNECT_CLAIM_PHONE_NUMBER_VERIFIED = 'phone_number_verified';
    /**
     * End-User's preferred postal address. The value of the address member is a JSON [RFC4627] structure.
     * @see https://openid.net/specs/openid-connect-core-1_0.html#AddressClaim
     * @since 1.0.0
     * @see \rhertogh\Yii2Oauth2Server\interfaces\components\openidconnect\claims\Oauth2OidcAddressClaimInterface
     */
    public const OPENID_CONNECT_CLAIM_ADDRESS = 'address';

    /**
     * Default claims specified by OpenID Connect
     * @see https://openid.net/specs/openid-connect-core-1_0.html#StandardClaims
     */
    public const OPENID_CONNECT_STANDARD_CLAIMS = [
        self::OPENID_CONNECT_CLAIM_SUB,
        self::OPENID_CONNECT_CLAIM_AUTH_TIME,
        self::OPENID_CONNECT_CLAIM_NONCE,
        self::OPENID_CONNECT_CLAIM_NAME,
        self::OPENID_CONNECT_CLAIM_FAMILY_NAME,
        self::OPENID_CONNECT_CLAIM_GIVEN_NAME,
        self::OPENID_CONNECT_CLAIM_MIDDLE_NAME,
        self::OPENID_CONNECT_CLAIM_NICKNAME,
        self::OPENID_CONNECT_CLAIM_PREFERRED_USERNAME,
        self::OPENID_CONNECT_CLAIM_PROFILE,
        self::OPENID_CONNECT_CLAIM_PICTURE,
        self::OPENID_CONNECT_CLAIM_WEBSITE,
        self::OPENID_CONNECT_CLAIM_GENDER,
        self::OPENID_CONNECT_CLAIM_BIRTHDATE,
        self::OPENID_CONNECT_CLAIM_ZONEINFO,
        self::OPENID_CONNECT_CLAIM_LOCALE,
        self::OPENID_CONNECT_CLAIM_UPDATED_AT,
        self::OPENID_CONNECT_CLAIM_EMAIL,
        self::OPENID_CONNECT_CLAIM_EMAIL_VERIFIED,
        self::OPENID_CONNECT_CLAIM_PHONE_NUMBER,
        self::OPENID_CONNECT_CLAIM_PHONE_NUMBER_VERIFIED,
        self::OPENID_CONNECT_CLAIM_ADDRESS,
    ];

    /**
     * Get the identifier for this claim, this can be a OpenID Connect standard claim or a custom one.
     * @return string
     * @see OPENID_CONNECT_STANDARD_CLAIMS
     * @see https://openid.net/specs/openid-connect-core-1_0.html#StandardClaims
     * @since 1.0.0
     */
    public function getIdentifier();

    /**
     * Set the identifier for this claim, this can be a OpenID Connect standard claim or a custom one.
     * @param string $identifier
     * @return $this
     * @see OPENID_CONNECT_STANDARD_CLAIMS
     * @see https://openid.net/specs/openid-connect-core-1_0.html#StandardClaims
     * @since 1.0.0
     */
    public function setIdentifier($identifier);

    /**
     * Get the value determiner for this claim.
     * @return string|array|callable If not set it will return claim's identifier.
     * See `setDeterminer()` for behavior per type.
     * @see setDeterminer()
     * @see getIdentifier()
     * @since 1.0.0
     */
    public function getDeterminer();

    /**
     * Set the value determiner for this claim.
     * @param string|callable|array $determiner The behavior of the determiner depends on its type:
     * string: Get the property by its name as set by the determiner, e.g. "email".
     *         Hint: if your user identity class extends \yii\base\BaseObject (which it probably is)
     *         the BaseObject will try to return a value for the getter. In our example `getEmail()`.
     * callable: The callable will be called in order to get the value. The format of the callable should be:
     *           ```php
     *           function (Oauth2OidcUserInterface $userIdentity, Oauth2OidcClaimInterface $claim, Oauth2Module $module) {
     *               return "your return value";
     *           }
     *           ```
     * array: Get a nested property, e.g. ['address', 'street_name'].
     *        Note: if the array is callable it will be treated as such (e.g. [$myObject, 'myCallback']),
     * @return $this
     * @since 1.0.0
     */
    public function setDeterminer($determiner);

    /**
     * Get the default value for the claim.
     * Used when the determiner is a string or array and the property can not be found.
     * @return mixed
     * @since 1.0.0
     */
    public function getDefaultValue();

    /**
     * Set the default value for the claim.
     * Used when the determiner is a string or array and the property can not be found.
     * @param mixed $defaultValue
     * @return $this
     * @since 1.0.0
     */
    public function setDefaultValue($defaultValue);
}
