<?php

namespace rhertogh\Yii2Oauth2Server\controllers\console\encryption;

use rhertogh\Yii2Oauth2Server\controllers\console\Oauth2EncryptionController;
use rhertogh\Yii2Oauth2Server\interfaces\controllers\console\encryption\Oauth2GenerateSecretActionInterface;
use yii\base\Action;
use yii\base\Exception;
use yii\base\InvalidConfigException;
use yii\console\ExitCode;
use yii\helpers\Json;

/**
 * @property Oauth2EncryptionController $controller
 */
class Oauth2GenerateSecretAction extends Action implements Oauth2GenerateSecretActionInterface
{
    /**
     * Generate a new random secret.
     *
     * @return int
     * @throws Exception
     * @throws InvalidConfigException
     */
    public function run()
    {
        $module = $this->controller->module;
        $keyName = $this->controller->keyName;
        $secretLength = $this->controller->secretLength;

        $secret = \Yii::$app->getSecurity()->generateRandomString($secretLength);
        $encryptedSecret = $module->getCryptographer()->encryp($secret, $keyName);

        $this->controller->stdout(Json::encode([
            'secret' => $secret,
            'encryptedSecret' => $encryptedSecret,
        ]));

        return ExitCode::OK;
    }
}
