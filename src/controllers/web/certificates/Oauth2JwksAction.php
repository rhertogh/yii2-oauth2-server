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
     * https://datatracker.ietf.org/doc/html/rfc7517
     */
    public function run()
    {
        $module = $this->controller->module;

        $publicKey = $module->getPublicKey();

        $keyInfo = openssl_pkey_get_details(openssl_pkey_get_public($publicKey->getKeyContents()));

        // https://datatracker.ietf.org/doc/html/rfc7518#section-6.3
        $keys = [new JWK([
            //ToDo 'kid' => '',
            'kty' => 'RSA',
            'alg' => 'RS256',
            'use' => 'sig',
            'n' => rtrim(StringHelper::base64UrlEncode($keyInfo['rsa']['n']), '='),
            'e' => rtrim(StringHelper::base64UrlEncode($keyInfo['rsa']['e']), '='),
        ])];

        return new JWKSet($keys);
    }
}
