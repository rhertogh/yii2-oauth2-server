<?php

namespace rhertogh\Yii2Oauth2Server\interfaces\components\encryption;

interface Oauth2EncryptorInterface
{

    /**
     * Set the available encryption keys.
     * @param string[] $keys
     * @since 1.0.0
     */
    public function setKeys($keys);

    /**
     * Set the name default encryption key (must be present in the available keys).
     * @param string $defaultKeyName
     * @see setKeys()
     * @since 1.0.0
     */
    public function setDefaultKeyName($name);

    /**
     * Encrypt the specified data.
     * @param string $data
     * @param string|null $keyName The name of the key to use for the encryption
     * (must be present in the available keys).
     * if `null` the default key name will be used.
     * @return string The encrypted data in format "keyName:encrypted_data".
     * @see setKeys()
     * @see setDefaultKeyName()
     * @since 1.0.0
     */
    public function encryp($data, $keyName = null);

    /**
     * Decrypt the specified data, the key (identified by the "keyName" part of the data)
     * must be present in the available keys.
     * @param string $data in format "keyName:encrypted_data".
     * @return string
     * @since 1.0.0
     */
    public function decrypt($data);
}
