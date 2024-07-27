<?php

namespace rhertogh\Yii2Oauth2Server\components\openidconnect\server\responses;

// phpcs:disable Generic.Files.LineLength.TooLong
use League\OAuth2\Server\Entities\AccessTokenEntityInterface;
use rhertogh\Yii2Oauth2Server\components\server\responses\Oauth2BearerTokenResponse;
use rhertogh\Yii2Oauth2Server\interfaces\components\openidconnect\request\Oauth2OidcAuthenticationRequestInterface;
use rhertogh\Yii2Oauth2Server\interfaces\components\openidconnect\scope\Oauth2OidcScopeInterface;
use rhertogh\Yii2Oauth2Server\interfaces\components\openidconnect\server\responses\Oauth2OidcBearerTokenResponseInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\external\user\Oauth2OidcUserInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\Oauth2AccessTokenInterface;
use Yii;
use yii\base\InvalidArgumentException;
use yii\base\InvalidConfigException;
use yii\helpers\ArrayHelper;
// phpcs:enable Generic.Files.LineLength.TooLong

class Oauth2OidcBearerTokenResponse extends Oauth2BearerTokenResponse implements Oauth2OidcBearerTokenResponseInterface
{
    /**
     * @inheritDoc
     * @param Oauth2AccessTokenInterface $accessToken
     * @return array
     * @throws InvalidConfigException
     */
    protected function getExtraParams(AccessTokenEntityInterface $accessToken)
    {
        $extraParams = parent::getExtraParams($accessToken);

        $scopeIdentifiers = array_map(fn($scope) => $scope->getIdentifier(), $accessToken->getScopes());

        // Not a OpenId Connect request if OpenId scope is not present.
        if (!in_array(Oauth2OidcScopeInterface::OPENID_CONNECT_SCOPE_OPENID, $scopeIdentifiers)) {
            return $extraParams;
        }

        $module = $this->getModule();

        $user = $module->getUserRepository()->getUserEntityByIdentifier($accessToken->getUserIdentifier());
        if ($user === null) {
            throw new InvalidArgumentException(
                'No user with identifier "' . $accessToken->getUserIdentifier() . '" found.'
            );
        }
        if (!($user instanceof Oauth2OidcUserInterface)) {
            throw new InvalidConfigException(
                get_class($user) . ' must implement ' . Oauth2OidcUserInterface::class
            );
        }

        $nonce = Yii::$app->request->post(Oauth2OidcAuthenticationRequestInterface::REQUEST_PARAMETER_NONCE);

        $token = $module->generateOpenIdConnectUserClaimsToken(
            $user,
            $accessToken->getClient()->getIdentifier(),
            $this->privateKey,
            $scopeIdentifiers,
            $nonce,
            $accessToken->getExpiryDateTime()
        );

        return ArrayHelper::merge($extraParams, [
            static::TOKEN_RESPONSE_ID_TOKEN => $token->toString()
        ]);
    }
}
