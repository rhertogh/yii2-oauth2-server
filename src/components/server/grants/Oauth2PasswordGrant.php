<?php

namespace rhertogh\Yii2Oauth2Server\components\server\grants;

use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Grant\PasswordGrant;
use Psr\Http\Message\ServerRequestInterface;
use rhertogh\Yii2Oauth2Server\components\server\grants\traits\Oauth2GrantTrait;
use rhertogh\Yii2Oauth2Server\exceptions\Oauth2ServerException;
use rhertogh\Yii2Oauth2Server\interfaces\components\server\grants\Oauth2PasswordGrantInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\external\user\Oauth2UserInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\Oauth2ClientInterface;
use Yii;
use yii\base\InvalidConfigException;

class Oauth2PasswordGrant extends PasswordGrant implements Oauth2PasswordGrantInterface
{
    use Oauth2GrantTrait;

    /**
     * @inheritDoc
     */
    protected function validateUser(ServerRequestInterface $request, ClientEntityInterface $client)
    {
        if (!($client instanceof Oauth2ClientInterface)) {
            throw new InvalidConfigException(get_class($client) . ' must implement ' . Oauth2UserInterface::class);
        }

        $user = parent::validateUser($request, $client);
        if (!($user instanceof Oauth2UserInterface)) {
            throw new InvalidConfigException(
                'Yii::$app->user->identity (currently ' . get_class($user)
                . ') must implement ' . Oauth2UserInterface::class
            );
        }

        if ($user->isOauth2ClientAllowed($client, $this->getIdentifier()) !== true) {
            throw Oauth2ServerException::accessDenied(
                Yii::t('oauth2', 'User {user_id} is not allowed to use client {client_identifier}.', [
                    'user_id' => $user->getId(),
                    'client_identifier' => $client->getIdentifier(),
                ])
            );
        }

        return $user;
    }
}
