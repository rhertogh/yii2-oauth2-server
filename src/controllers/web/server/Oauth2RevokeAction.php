<?php

namespace rhertogh\Yii2Oauth2Server\controllers\web\server;

use Defuse\Crypto\Crypto;
use Defuse\Crypto\Key;
use rhertogh\Yii2Oauth2Server\controllers\web\Oauth2ServerController;
use rhertogh\Yii2Oauth2Server\controllers\web\server\base\Oauth2BaseServerAction;
use rhertogh\Yii2Oauth2Server\helpers\Oauth2RequestHelper;
use rhertogh\Yii2Oauth2Server\interfaces\controllers\web\server\Oauth2RevokeActionInterface;
use rhertogh\Yii2Oauth2Server\Oauth2Module;
use Yii;
use yii\helpers\Json;
use yii\web\BadRequestHttpException;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;

/**
 * OAuth 2.0 Token Revocation (RFC 7009)
 *
 * @property Oauth2ServerController $controller
 * @see https://datatracker.ietf.org/doc/html/rfc7009
 */
class Oauth2RevokeAction extends Oauth2BaseServerAction implements Oauth2RevokeActionInterface
{
    public function run()
    {
        try {
            $module = $this->controller->module;

            if (!$module->enableTokenRevocation) {
                throw new NotFoundHttpException();
            }

            $tokenParsers = [
                'refresh_token' => [$this, 'parseTokenAsRefreshToken'],
                'access_token' => [$this, 'parseTokenAsAccessToken'],
            ];

            $request = Yii::$app->request;

            $token = $request->getBodyParam('token');
            if (empty($token)) {
                throw new BadRequestHttpException('The `token` body parameter is required.');
            }
            $tokenTypeHint = $request->getBodyParam('token_type_hint');
            if (!empty($tokenTypeHint)) {

                if (in_array($tokenTypeHint, array_keys($tokenParsers))) {
                    // Move hinted type to beginning.
                    $tokenParsers = [$tokenTypeHint => $tokenParsers[$tokenTypeHint]] + $tokenParsers;
                } else {
                    Yii::getLogger()->log(
                        'The client specified an unknown `token_type_hint` "' . $tokenTypeHint . '".',
                        $module->getElaboratedHttpClientErrorsLogLevel(),
                        __METHOD__
                    );
                }
            }

            foreach ($tokenParsers as $tokenParser) {
                $parseResult = call_user_func($tokenParser, $module, $token, $tokenTypeHint);
                if ($parseResult) {
                    $clientIdentifier = $parseResult['clientIdentifier'] ?? null;
                    $refreshTokenIdentifier = $parseResult['refreshTokenIdentifier'] ?? null;
                    $accessTokenIdentifier = $parseResult['accessTokenIdentifier'] ?? null;
                    break;
                }
            }

            if (empty($clientIdentifier) || empty($accessTokenIdentifier)) {
                throw new BadRequestHttpException('Unable to resolve the `token` parameter to a valid token type.');
            }

            $client = $module->getClientRepository()->findModelByIdentifier($clientIdentifier);
            if (!$client) {
                throw new BadRequestHttpException('The `client_id` "' . $clientIdentifier . '" specified in the token is not valid.'); // phpcs:ignore Generic.Files.LineLength.TooLong
            }

            if ($client->isConfidential()) {
                try {
                    [$credentialsClientIdentifier, $clientSecret] = Oauth2RequestHelper::getClientCredentials($request);
                } catch (\Exception $exception) {
                }

                if (empty($credentialsClientIdentifier) || empty($clientSecret)) {
                    throw new ForbiddenHttpException('Client authentication is required for confidential clients.');
                }

                if (
                    !Yii::$app->security->compareString($client->getIdentifier(), $credentialsClientIdentifier)
                    || !$client->validateSecret($clientSecret, $module->getCryptographer())
                ) {
                    throw new ForbiddenHttpException('Invalid client authentication.');
                }
            }

            if (!empty($refreshTokenIdentifier)) {
                $module->getRefreshTokenRepository()->revokeRefreshToken($refreshTokenIdentifier);
            }
            $module->getAccessTokenRepository()->revokeAccessToken($accessTokenIdentifier);

            return Yii::$app->response;
        } catch (\Exception $e) {
            return $this->processException($e, __METHOD__);
        }
    }

    protected function parseTokenAsRefreshToken(Oauth2Module $module, string $token, string $tokenTypeHint)
    {
        try {
            $refreshTokenString = Crypto::decrypt($token, Key::loadFromAsciiSafeString($module->codesEncryptionKey));
            $refreshToken = Json::decode($refreshTokenString);
        } catch (\Throwable $e) {
            if ($tokenTypeHint === 'refresh_token') {
                Yii::getLogger()->log(
                    'The client specified the `token_type_hint` as "refresh_token",'
                        . ' however the server is unable to parse the `token` as such: ' . $e,
                    $module->getElaboratedHttpClientErrorsLogLevel(),
                    __METHOD__
                );
            }
            unset($e);
        }

        if (!empty($refreshToken['refresh_token_id'])) {
            Yii::debug('Found refresh token: ' . $refreshTokenString, __METHOD__);
            $refreshTokenIdentifier = $refreshToken['refresh_token_id'];

            if (empty($refreshToken['access_token_id'])) {
                throw new BadRequestHttpException('The `access_token_id` must be specified in the refresh token.');
            }

            $accessTokenIdentifier = $refreshToken['access_token_id'];

            if (empty($refreshToken['client_id'])) {
                throw new BadRequestHttpException('The `client_id` must be specified in the refresh token.');
            }

            $clientIdentifier = $refreshToken['client_id'];

            return [
                'clientIdentifier' => $clientIdentifier,
                'refreshTokenIdentifier' => $refreshTokenIdentifier,
                'accessTokenIdentifier' => $accessTokenIdentifier
            ];
        }

        return null;
    }

    protected function parseTokenAsAccessToken(Oauth2Module $module, string $token, string $tokenTypeHint)
    {
        try {
            $accessTokenClaims = $module->getAccessTokenClaims($token);
            $accessTokenIdentifier = $accessTokenClaims->get('jti');
            $clientIdentifier = $accessTokenClaims->get('client_id');

            return [
                'clientIdentifier' => $clientIdentifier,
                'accessTokenIdentifier' => $accessTokenIdentifier
            ];
        } catch (\Throwable $e) {
            if ($tokenTypeHint === 'access_token') {
                Yii::getLogger()->log(
                    'The client specified the `token_type_hint` as "access_token",'
                        . ' however the server is unable to parse the `token` as such: ' . $e,
                    $module->getElaboratedHttpClientErrorsLogLevel(),
                    __METHOD__
                );
            }
            unset($e);
        }
        return null;
    }
}
