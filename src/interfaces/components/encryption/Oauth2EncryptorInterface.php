<?php

namespace rhertogh\Yii2Oauth2Server\interfaces\components\encryption;

interface Oauth2EncryptorInterface
{
    /**
     * Set the available encryption keys.
     * @param string|string[] $keys
     * @since 1.0.0
     */
    public function setKeys($keys);

    /**
     * Get the name of the default encryption key.
     * @see setDefaultKeyName()
     * @since 1.0.0
     */
    public function getDefaultKeyName();

    /**
     * Set the name of the default encryption key (must be present in the available keys).
     * @param string $name
     * @see setKeys()
     * @since 1.0.0
     */
    public function setDefaultKeyName($name);

    /**
     * Check if there is a key with the specified name
     *
     * @param string $name
     * @since 1.0.0
     */
    public function hasKey($name);

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
     * Parses the raw data into a keyName and ciphertext
     *
     * @param string $data
     * @return false|array{keyName: string, ciphertext: string}
     * @since 1.0.0
     */
    public function parseData($data);

    /**
     * Decrypt the specified data, the key (identified by the "keyName" part of the data)
     * must be present in the available keys.
     * @param string $data in format "keyName:encrypted_data".
     * @return string
     * @since 1.0.0
     */
    public function decrypt($data);

    /**
     * Rotates the encryption key by decrypting the data and encrypting it with a new key.
     * must be present in the available keys.
     * @param string $data in format "keyName:encrypted_data".
     * @param string|null $newKeyName The name of the new key to use for the encryption
     * (must be present in the available keys).
     * if `null` the default key name will be used.
     * @return string
     * @see decrypt()
     * @see encryp()
     * @since 1.0.0
     */
    public function rotateKey($data, $newKeyName = null);
}
