<?php

namespace rhertogh\Yii2Oauth2Server\components\openidconnect\server;

use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Signer\Rsa\Sha256;
use League\OAuth2\Server\Entities\AccessTokenEntityInterface;
use League\OAuth2\Server\ResponseTypes\BearerTokenResponse;
use rhertogh\Yii2Oauth2Server\components\openidconnect\scopes\Oauth2OidcScope;
use rhertogh\Yii2Oauth2Server\helpers\OpenIdConnectHelper;
use rhertogh\Yii2Oauth2Server\interfaces\components\openidconnect\request\Oauth2OidcAuthenticationRequestInterface;
use rhertogh\Yii2Oauth2Server\interfaces\components\openidconnect\scope\Oauth2OidcClaimInterface;
use rhertogh\Yii2Oauth2Server\interfaces\components\openidconnect\server\Oauth2OidcBearerTokenResponseInterface;
use rhertogh\Yii2Oauth2Server\interfaces\components\openidconnect\scope\Oauth2OidcScopeCollectionInterface;
use rhertogh\Yii2Oauth2Server\interfaces\components\openidconnect\scope\Oauth2OidcScopeInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\Oauth2AccessTokenInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\Oauth2OidcUserInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\Oauth2UserInterface;
use rhertogh\Yii2Oauth2Server\Oauth2Module;
use Yii;
use yii\base\InvalidArgumentException;
use yii\base\InvalidConfigException;

class Oauth2OidcBearerTokenResponse extends BearerTokenResponse implements Oauth2OidcBearerTokenResponseInterface
{
    /**
     * @var Oauth2Module
     */
    protected $_module;

    /**
     * @inheritDoc
     */
    public function __construct(Oauth2Module $module)
    {
        $this->_module = $module;
    }

    /**
     * @inheritDoc
     */
    public function getModule()
    {
        return $this->_module;
    }

    /**
     * @inheritDoc
     * @param Oauth2AccessTokenInterface $accessToken
     * @return array
     * @throws InvalidConfigException
     */
    protected function getExtraParams(AccessTokenEntityInterface $accessToken)
    {
        $scopeIdentifiers = array_map(fn($scope) => $scope->getIdentifier(), $accessToken->getScopes());

        // Not a OpenId Connect request if OpenId scope is not present.
        if (!in_array(Oauth2OidcScopeInterface::OPENID_CONNECT_SCOPE_OPENID, $scopeIdentifiers)) {
            return [];
        }

        $module = $this->getModule();

        /** @var Oauth2UserInterface $user */
        $user = $module->getUserRepository()->getUserEntityByIdentifier($accessToken->getUserIdentifier());

        if ($user === null) {
            throw new InvalidArgumentException(
                'No user with identifier "' . $accessToken->getUserIdentifier() . '" found.'
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

        return [
            static::TOKEN_RESPONSE_ID_TOKEN => $token->toString()
        ];
    }
}
