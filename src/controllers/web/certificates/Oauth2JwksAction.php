<?php

namespace rhertogh\Yii2Oauth2Server\controllers\web\certificates;

use Jose\Component\Core\JWK;
use Jose\Component\Core\JWKSet;
use Jose\Component\KeyManagement\KeyConverter\RSAKey;
use League\OAuth2\Server\CryptKey;
use rhertogh\Yii2Oauth2Server\controllers\web\Oauth2CertificatesController;
use Yii;
use yii\base\Action;
use yii\helpers\StringHelper;

/**
 * @property Oauth2CertificatesController $controller
 */
class Oauth2JwksAction extends Action
{
    /**
     * RFC7517: JSON Web Key (JWK)
     * https://datatracker.ietf.org/doc/html/rfc7517
     *
     * For algorithms see RFC7518: JSON Web Algorithms - Parameters for RSA Keys
     * https://datatracker.ietf.org/doc/html/rfc7518#section-6.3.
     */
    public function run()
    {
        $module = $this->controller->module;

        $publicKey = $module->getPublicKey();

        $keyInfo = openssl_pkey_get_details(openssl_pkey_get_public($publicKey->getKeyContents()));

        $keys = [new JWK([
            // ToDo 'kid' => '', // https://datatracker.ietf.org/doc/html/rfc7517#section-4.5.
            'kty' => 'RSA',
            'alg' => 'RS256', // https://datatracker.ietf.org/doc/html/rfc7518#section-6.3.
            'use' => 'sig',
            'n' => rtrim(StringHelper::base64UrlEncode($keyInfo['rsa']['n']), '='),
            'e' => rtrim(StringHelper::base64UrlEncode($keyInfo['rsa']['e']), '='),
        ])];

        return new JWKSet($keys);
    }
}
