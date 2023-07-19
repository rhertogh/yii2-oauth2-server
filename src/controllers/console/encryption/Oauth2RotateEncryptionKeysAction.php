<?php

namespace rhertogh\Yii2Oauth2Server\controllers\console\encryption;

use rhertogh\Yii2Oauth2Server\controllers\console\Oauth2EncryptionController;
use yii\base\Action;
use yii\base\InvalidArgumentException;
use yii\console\ExitCode;
use yii\console\widgets\Table;
use yii\helpers\Console;

/**
 * @property Oauth2EncryptionController $controller
 */
class Oauth2RotateEncryptionKeysAction extends Action
{
    public function run()
    {
        $module = $this->controller->module;
        $newKeyName = $this->controller->keyName;

        if (empty($newKeyName)) {
            $newKeyName = $module->getCryptographer()->getDefaultKeyName();
        }

        if (!$module->getCryptographer()->hasKey($newKeyName)) {
            throw new InvalidArgumentException('No key with name "' . $newKeyName . '" available.');
        }

        if ($this->controller->confirm('Rotate all encryption keys to key "' . $newKeyName . '"?')) {
            $result = $module->rotateStorageEncryptionKeys($newKeyName);

            $this->controller->stdout("Updated models:" . PHP_EOL);
            $this->controller->stdout(Table::widget([
                'headers' => ['Model', 'Num Updated'],
                'rows' => array_map(fn($model) => [$model, $result[$model]], array_keys($result)),
            ]));
            $this->controller->stdout('Successfully rotated encryption keys.'  . PHP_EOL, Console::FG_GREEN);
        }

        return ExitCode::OK;
    }
}
