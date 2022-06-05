<?php

namespace Yii2Oauth2ServerTests\_helpers;

use rhertogh\Yii2Oauth2Server\components\openidconnect\claims\Oauth2OidcAddressClaim;
use rhertogh\Yii2Oauth2Server\interfaces\models\external\user\Oauth2OidcUserInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\external\user\Oauth2OidcUserSessionStatusInterface;
use rhertogh\Yii2Oauth2Server\traits\models\Oauth2OidcUserIdentityTrait;

// phpcs:disable Generic.Files.LineLength.TooLong -- Sample documentation
class TestUserModelOidc extends TestUserModel implements
    Oauth2OidcUserInterface, # Optional interface, only required when 'Open ID Connect' is used
    Oauth2OidcUserSessionStatusInterface # Optional interface, only required when 'openIdConnectIssueRefreshTokenWithoutOfflineAccessScope' is enabled
{
    use Oauth2OidcUserIdentityTrait; # Helper trait resolve Open ID Connect claims

    // phpcs:enable Generic.Files.LineLength.TooLong

    // placeholder for test setting.
    public static $hasActiveSession = false;

    /**
     * @inheritDoc
     */
    public function getLatestAuthenticatedAt()
    {
        return (new \DateTimeImmutable())->setTimestamp($this->latest_authenticated_at);
    }

    /**
     * @inheritDoc
     */
    public function hasActiveSession()
    {
        // use test setting.
        return static::$hasActiveSession;
    }

    public function getNickname()
    {
        return $this->username;
    }


    public function getAddress()
    {
        return new Oauth2OidcAddressClaim([
            'formatted' => "123 Elf Road\nXM4 5HQ, Santa's Grotto\nReindeerland, North Pole",
            'streetAddress' => '123 Elf Road',
            'locality' => "Santa's Grotto",
            'region' => 'Reindeerland',
            'postalCode' => 'XM4 5HQ',
            'country' => 'North Pole',
        ]);
    }
}
