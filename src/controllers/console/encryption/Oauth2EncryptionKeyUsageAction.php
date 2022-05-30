<?php

namespace rhertogh\Yii2Oauth2Server\controllers\console\encryption;

use rhertogh\Yii2Oauth2Server\controllers\console\Oauth2EncryptionController;
use yii\base\Action;
use yii\console\ExitCode;
use yii\console\widgets\Table;

/**
 * @property Oauth2EncryptionController $controller
 */
class Oauth2EncryptionKeyUsageAction extends Action
{
    public function run()
    {
        $module = $this->controller->module;
        $modelUsage = $module->getStorageEncryptionKeyUsage();

        if ($this->controller->keyName) {
            $keyName = $this->controller->keyName;
            $result = [];
            foreach ($modelUsage as $model => $modelResult) {
                if (array_key_exists($keyName, $modelResult)) {
                    $result[$model] = $modelResult[$keyName];
                } else {
                    $result[$model] = '[none]';
                }
            }

            $this->controller->stdout('Storage Encryption Key Usage for "' . $keyName . '":' . PHP_EOL);
            $this->controller->stdout(Table::widget([
                'headers' => ['Model', 'Usage'],
                'rows' => array_map(fn($model) => [$model, $result[$model]], array_keys($result)),
            ]));
        } else {
            $result = [];
            foreach ($modelUsage as $modelResult) {
                foreach ($modelResult as $keyName => $modelPks) {
                    if (array_key_exists($keyName, $result)) {
                        $result[$keyName] += count($modelPks);
                    } else {
                        $result[$keyName] = count($modelPks);
                    }
                }
            }

            $this->controller->stdout('Storage Encryption Keys Usage:' . PHP_EOL);
            $this->controller->stdout(Table::widget([
                'headers' => ['Key', 'Usage'],
                'rows' => array_map(fn($keyName) => [$keyName, $result[$keyName]], array_keys($result)),
            ]));
        }

        return ExitCode::OK;
    }
}
