<?php

namespace rhertogh\Yii2Oauth2Server\components\server\grants\traits;

use League\OAuth2\Server\Entities\AccessTokenEntityInterface;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use Psr\Http\Message\ServerRequestInterface;
use rhertogh\Yii2Oauth2Server\events\Oauth2AccessTokenIssuanceEvent;
use rhertogh\Yii2Oauth2Server\events\Oauth2AuthCodeIssuanceEvent;
use rhertogh\Yii2Oauth2Server\events\Oauth2RefreshTokenIssuanceEvent;
use rhertogh\Yii2Oauth2Server\helpers\UrlHelper;
use rhertogh\Yii2Oauth2Server\interfaces\models\Oauth2ClientInterface;
use rhertogh\Yii2Oauth2Server\Oauth2Module;
use Yii;
use yii\base\InvalidConfigException;

trait Oauth2GrantTrait
{
    /** @var Oauth2Module */
    public $module;

    protected function issueAuthCode(
        \DateInterval $authCodeTTL,
        ClientEntityInterface $client,
        $userIdentifier,
        $redirectUri,
        array $scopes = []
    ) {
        /** @var Oauth2AuthCodeIssuanceEvent $event */
        $event = Yii::createObject([
            'class' => Oauth2AuthCodeIssuanceEvent::class,
            'grant' => $this,
            'authCodeTTL' => $authCodeTTL,
            'client' => $client,
            'userIdentifier' => $userIdentifier,
            'redirectUri' => $redirectUri,
            'scopes' => $scopes,
        ]);

        $this->module->trigger(Oauth2Module::EVENT_BEFORE_AUTH_CODE_ISSUANCE, $event);

        if (!$event->authCode) {
            $event->authCode = parent::issueAuthCode($authCodeTTL, $client, $userIdentifier, $redirectUri, $scopes);
        }

        $this->module->trigger(Oauth2Module::EVENT_AFTER_AUTH_CODE_ISSUANCE, $event);

        return $event->authCode;
    }

    protected function issueAccessToken(
        \DateInterval $accessTokenTTL,
        ClientEntityInterface $client,
        $userIdentifier,
        array $scopes = []
    ) {
        /** @var Oauth2AccessTokenIssuanceEvent $event */
        $event = Yii::createObject([
            'class' => Oauth2AccessTokenIssuanceEvent::class,
            'grant' => $this,
            'accessTokenTTL' => $accessTokenTTL,
            'client' => $client,
            'userIdentifier' => $userIdentifier,
            'scopes' => $scopes,
        ]);

        $this->module->trigger(Oauth2Module::EVENT_BEFORE_ACCESS_TOKEN_ISSUANCE, $event);

        if (!$event->accessToken) {
            $event->accessToken = parent::issueAccessToken($accessTokenTTL, $client, $userIdentifier, $scopes);
        }

        $this->module->trigger(Oauth2Module::EVENT_AFTER_ACCESS_TOKEN_ISSUANCE, $event);

        return $event->accessToken;
    }

    protected function issueRefreshToken(AccessTokenEntityInterface $accessToken)
    {
        /** @var Oauth2RefreshTokenIssuanceEvent $event */
        $event = Yii::createObject([
            'class' => Oauth2RefreshTokenIssuanceEvent::class,
            'grant' => $this,
            'accessToken' => $accessToken,
        ]);

        $this->module->trigger(Oauth2Module::EVENT_BEFORE_REFRESH_TOKEN_ISSUANCE, $event);

        if (!$event->refreshToken) {
            $event->refreshToken = parent::issueRefreshToken($accessToken);
        }

        $this->module->trigger(Oauth2Module::EVENT_AFTER_REFRESH_TOKEN_ISSUANCE, $event);

        return $event->refreshToken;
    }

    protected function validateRedirectUri(
        string $redirectUri,
        ClientEntityInterface $client,
        ServerRequestInterface $request
    ) {
        if (!($client instanceof Oauth2ClientInterface)) {
            throw new InvalidConfigException(get_class($client) . ' must implement ' . Oauth2ClientInterface::class);
        }

        if ($client->isVariableRedirectUriQueryAllowed()) {
            $redirectUri = UrlHelper::stripQueryAndFragment($redirectUri);
        }

        parent::validateRedirectUri($redirectUri, $client, $request);
    }
}
