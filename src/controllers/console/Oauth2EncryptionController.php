<?php

namespace rhertogh\Yii2Oauth2Server\controllers\console;

use rhertogh\Yii2Oauth2Server\controllers\console\base\Oauth2BaseConsoleController;
use rhertogh\Yii2Oauth2Server\controllers\console\encryption\Oauth2EncryptionKeyUsageAction;
use rhertogh\Yii2Oauth2Server\controllers\console\encryption\Oauth2GenerateSecretAction;
use rhertogh\Yii2Oauth2Server\controllers\console\encryption\Oauth2RotateEncryptionKeysAction;
use yii\helpers\ArrayHelper;

class Oauth2EncryptionController extends Oauth2BaseConsoleController
{
    /**
     * @var string|null
     */
    public $keyName = null;
    public $secretLength = 32;

    /**
     * @inheritDoc
     */
    public function options($actionID)
    {
        $options = parent::options($actionID);
        if (in_array($actionID, ['key-usage', 'rotate-keys', 'generate-secret'])) {
            $options = ArrayHelper::merge($options, [
                'keyName',
            ]);
        }
        if ($actionID === 'generate-secret') {
            $options = ArrayHelper::merge($options, [
                'secretLength',
            ]);
        }
        return $options;
    }

    public function optionAliases()
    {
        return ArrayHelper::merge(parent::optionAliases(), [
            'k' => 'keyName',
            'l' => 'secretLength',
        ]);
    }

    /**
     * @inheritDoc
     */
    public function actions()
    {
        return [
            'key-usage' => Oauth2EncryptionKeyUsageAction::class,
            'rotate-keys' => Oauth2RotateEncryptionKeysAction::class,
            'generate-secret' => Oauth2GenerateSecretAction::class,
        ];
    }
}
