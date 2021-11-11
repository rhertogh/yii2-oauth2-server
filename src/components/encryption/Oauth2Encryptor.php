<?php

namespace rhertogh\Yii2Oauth2Server\components\encryption;

use Defuse\Crypto\Crypto;
use Defuse\Crypto\Exception\BadFormatException;
use Defuse\Crypto\Exception\EnvironmentIsBrokenException;
use Defuse\Crypto\Key;
use rhertogh\Yii2Oauth2Server\interfaces\components\encryption\Oauth2EncryptorInterface;
use rhertogh\Yii2Oauth2Server\interfaces\components\factories\encryption\Oauth2EncryptionKeyFactoryInterface;
use Yii;
use yii\base\Component;
use yii\base\InvalidConfigException;

class Oauth2Encryptor extends Component implements Oauth2EncryptorInterface
{
    /**
     * Separator between different parts in the data. E.g. the keyName and secret.
     * @var string
     */
    public $dataSeparator = '::';

    /**
     * @var Key[]|null
     */
    protected $_keys = null;

    /**
     * @var string|null
     */
    protected $_defaultKeyName = null;

    /**
     * @inheritDoc
     * @throws InvalidConfigException
     */
    public function setKeys($keys)
    {
        /** @var Oauth2EncryptionKeyFactoryInterface $keyFactory */
        $keyFactory = Yii::createObject(Oauth2EncryptionKeyFactoryInterface::class);
        $this->_keys = [];
        foreach ($keys as $keyName => $key) {
            try {
                $this->_keys[$keyName] = $keyFactory->createFromAsciiSafeString($key);
            } catch (BadFormatException $e) {
                throw new InvalidConfigException('Encryption key "' . $keyName . '" is malformed: ' . $e->getMessage(), 0, $e);
            } catch (EnvironmentIsBrokenException $e) {
                throw new InvalidConfigException('Could not instantiate key "' . $keyName . '": ' . $e->getMessage(), 0, $e);
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function setDefaultKeyName($name)
    {
        $this->_defaultKeyName = $name;
    }

    /**
     * @inheritDoc
     */
    public function encryp($data, $keyName = null)
    {
        if (empty($keyName)) {
            if (empty($this->_defaultKeyName)) {
                throw new \BadMethodCallException('Unable to encrypt, $keyName is empty and $defaultKeyName is not set');
            } else {
                $keyName = $this->_defaultKeyName;
            }
        }

        if (empty($this->_keys[$keyName])) {
            throw new \BadMethodCallException('Unable to encrypt, no key with name "' . $keyName . '" has been set');
        }

        if (empty($this->dataSeparator)) {
            throw new InvalidConfigException('Unable to encrypt, dataSeparator is empty');
        }

        if (strpos($keyName, $this->dataSeparator) !== false) {
            throw new \BadMethodCallException('Unable to encrypt, key name "' . $keyName . '" contains dataSeparator "' . $this->dataSeparator . '"');
        }

        return $keyName
            . $this->dataSeparator
            . base64_encode(Crypto::encrypt($data, $this->_keys[$keyName], true));
    }

    /**
     * @inheritDoc
     */
    public function decrypt($data)
    {
        try {
            list($keyName, $ciphertext) = explode($this->dataSeparator, $data, 2);
        } catch (\Throwable $e) {
            throw new \InvalidArgumentException('Unable to decrypt, $data must be in format "keyName' . $this->dataSeparator . 'ciphertext"');
        }

        if (empty($this->_keys[$keyName])) {
            throw new \BadMethodCallException('Unable to decrypt, no key with name "' . $keyName . '" has been set');
        }

        return Crypto::decrypt(base64_decode($ciphertext), $this->_keys[$keyName], true);
    }
}
