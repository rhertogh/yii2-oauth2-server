<?php

namespace rhertogh\Yii2Oauth2Server\components\factories\encryption;

use Defuse\Crypto\Key;
use rhertogh\Yii2Oauth2Server\interfaces\components\factories\encryption\Oauth2EncryptionKeyFactoryInterface;
use yii\base\Component;

class Oauth2EncryptionKeyFactory extends Component implements Oauth2EncryptionKeyFactoryInterface
{
    /**
     * Default value for `createFromAsciiSafeString()` doNotTrim parameter.
     * @var bool
     */
    public $doNotTrim = false;

    /**
     * @inheritDoc
     */
    public function createFromAsciiSafeString($keyString, $doNotTrim = null)
    {
        return Key::loadFromAsciiSafeString($keyString, $doNotTrim !== null ? $doNotTrim : $this->doNotTrim);
    }
}
