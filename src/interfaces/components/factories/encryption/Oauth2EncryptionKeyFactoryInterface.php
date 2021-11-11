<?php

namespace rhertogh\Yii2Oauth2Server\interfaces\components\factories\encryption;

use Defuse\Crypto\Exception\BadFormatException;
use Defuse\Crypto\Exception\EnvironmentIsBrokenException;
use Defuse\Crypto\Key;

interface Oauth2EncryptionKeyFactoryInterface
{
    /**
     * Creates an encryption key from a string.
     * @param string $keyString
     * @param bool|null $doNotTrim Disable trimming of $keyString. If `null` use factory default.
     * @return Key
     * @throws BadFormatException
     * @throws EnvironmentIsBrokenException
     * @see Key::loadFromAsciiSafeString()
     * @since 1.0.0
     */
    public function createFromAsciiSafeString($keyString, $doNotTrim = null);
}
