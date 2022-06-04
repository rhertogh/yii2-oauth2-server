<?php

namespace rhertogh\Yii2Oauth2Server\components\server\grants;

use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Exception\OAuthServerException;
use League\OAuth2\Server\Grant\PasswordGrant;
use League\OAuth2\Server\ResponseTypes\ResponseTypeInterface;
use Psr\Http\Message\ServerRequestInterface;
use rhertogh\Yii2Oauth2Server\interfaces\components\server\grants\Oauth2PasswordGrantInterface;
use rhertogh\Yii2Oauth2Server\interfaces\components\user\Oauth2PasswordGrantUserComponentInterface;
use Yii;
use yii\web\User;

class Oauth2PasswordGrant extends PasswordGrant implements Oauth2PasswordGrantInterface
{
    /**
     * @var User|null
     */
    protected $validatedUser = null;

    /**
     * @inheritDoc
     */
    protected function validateUser(ServerRequestInterface $request, ClientEntityInterface $client)
    {
        $this->validatedUser = parent::validateUser($request, $client);
        if (Yii::$app->user instanceof Oauth2PasswordGrantUserComponentInterface) {
            if (!Yii::$app->user->beforeOauth2PasswordGrantLogin($this->validatedUser, $client, $this)) {
                Yii::info('Login rejected by `beforeOauthPasswordGrantLogin()`.', 'oauth2');
                $this->validatedUser = null;
                throw OAuthServerException::invalidCredentials();
            }
        }

        return $this->validatedUser;
    }

    /**
     * {@inheritdoc}
     */
    public function respondToAccessTokenRequest(
        ServerRequestInterface $request,
        ResponseTypeInterface $responseType,
        \DateInterval $accessTokenTTL
    ) {
        $responseType = parent::respondToAccessTokenRequest($request, $responseType, $accessTokenTTL);
        if (Yii::$app->user instanceof Oauth2PasswordGrantUserComponentInterface) {
            Yii::$app->user->afterOauth2PasswordGrantLogin($this->validatedUser, $this);
        }
        return $responseType;
    }
}
