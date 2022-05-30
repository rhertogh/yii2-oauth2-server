<?php

namespace rhertogh\Yii2Oauth2Server\interfaces\models\base;

use rhertogh\Yii2Oauth2Server\interfaces\components\encryption\Oauth2EncryptorInterface;

interface Oauth2EncryptedStorageInterface
{
    /**
     * Encrypt all models with a new key.
     *
     * @param Oauth2EncryptorInterface $encryptor
     * @param string|null $newKeyName The name of the new key to use for the encryption
     * (must be present in the available keys).
     * @return int Number of updated records
     * @since 1.0.0
     */
    public static function rotateStorageEncryptionKeys($encryptor, $newKeyName = null);

    /**
     * Get the encryption key usages
     *
     * @param Oauth2EncryptorInterface $encryptor
     * @return array
     * @since 1.0.0
     */
    public static function getUsedStorageEncryptionKeys($encryptor);

    /**
     * The attributes that are encrypted for this model
     *
     * @return string[]
     * @since 1.0.0
     */
    public static function getEncryptedAttributes();

    /**
     * Encrypt the model's data with a new key.
     *
     * @param Oauth2EncryptorInterface $encryptor
     * @param string|null $newKeyName The name of the new key to use for the encryption
     * (must be present in the available keys).
     * if `null` the default key name will be used.
     * @since 1.0.0
     */
    public function rotateStorageEncryptionKey($encryptor, $newKeyName = null);
}
