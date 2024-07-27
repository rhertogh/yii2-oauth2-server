<?php

namespace Yii2Oauth2ServerTests\unit\components\encryption;

use Defuse\Crypto\Exception\EnvironmentIsBrokenException;
use rhertogh\Yii2Oauth2Server\components\encryption\Oauth2Cryptographer;
use rhertogh\Yii2Oauth2Server\interfaces\components\factories\encryption\Oauth2EncryptionKeyFactoryInterface;
use Yii2Oauth2ServerTests\unit\TestCase;

/**
 * @covers \rhertogh\Yii2Oauth2Server\components\encryption\Oauth2Cryptographer
 */
class Oauth2CryptographerTest extends TestCase
{
    protected $keys = [
        // phpcs:disable Generic.Files.LineLength.TooLong -- readability actually better on single line
        'test' => 'def0000068fcf7a02625e841c263b227bb0ee04a42cb39b668a81e9b151e58f58d44fa15655e138a397b515482bea2688bd479647d41d084b82932215938d702f4e3b15c',
        'new' => 'def0000068fcf7a02625e841c263b227bb0ee04a42cb39b668a81e9b151e58f58d44fa15655e138a397b515482bea2688bd479647d41d084b82932215938d702f4e3b15c',
        // phpcs:enable Generic.Files.LineLength.TooLong
    ];

    public function testEncAndDecryption()
    {
        $this->mockConsoleApplication();
        $cryptographer = new Oauth2Cryptographer([
            'keys' => $this->keys,
            'defaultKeyName' => 'test',
        ]);
        $data = 'secret';

        $ciphertext = $cryptographer->encryp($data);
        $this->assertFalse(strpos($ciphertext, $data));

        $plaintext = $cryptographer->decrypt($ciphertext);
        $this->assertEquals($data, $plaintext);
    }

    public function testSetKeyInvalidKey()
    {
        $this->mockConsoleApplication();
        $this->expectExceptionMessage(
            'Encryption key "test" is malformed: Encoding::hexToBin() input is not a hex string.'
        );
        new Oauth2Cryptographer([
            'keys' => [
                'test' => 'malformed'
            ],
        ]);
    }


    public function testSetKeyBrokenEnvironment()
    {
        $this->mockConsoleApplication([
            'container' => [
                'definitions' => [
                    Oauth2EncryptionKeyFactoryInterface::class => new class implements Oauth2EncryptionKeyFactoryInterface { // phpcs:ignore Generic.Files.LineLength.TooLong
                        public function createFromAsciiSafeString($keyString, $doNotTrim = null)
                        {
                            throw new EnvironmentIsBrokenException('test message');
                        }
                    },
                ],
            ],
        ]);

        $this->expectExceptionMessage('Could not instantiate key "test": test message');
        new Oauth2Cryptographer([
            'keys' => $this->keys,
        ]);
    }

    public function testGetSetDefaultKeyName()
    {
        $cryptographer = new Oauth2Cryptographer();

        $cryptographer->setDefaultKeyName('test_default_key');
        $this->assertEquals('test_default_key', $cryptographer->getDefaultKeyName());
    }

    public function testGetDefaultKeyNameWithoutItBeingSet()
    {
        $cryptographer = new Oauth2Cryptographer();

        $this->expectExceptionMessage('Unable to get the defaultKeyName since it is not set.');
        $cryptographer->encryp('test');
    }

    public function testEncryptWithoutKey()
    {
        $cryptographer = new Oauth2Cryptographer();

        $this->expectExceptionMessage('Unable to encrypt, no key with name "non-existing" has been set');
        $cryptographer->encryp('test', 'non-existing');
    }

    public function testEncryptWithoutDataSeparator()
    {
        $this->mockConsoleApplication();
        $cryptographer = new Oauth2Cryptographer([
            'keys' => $this->keys,
            'defaultKeyName' => 'test',
            'dataSeparator' => '',
        ]);

        $this->expectExceptionMessage('Unable to encrypt, dataSeparator is empty');
        $cryptographer->encryp('test');
    }

    public function testEncryptWithoutKeyContainingDataSeparator()
    {
        $this->mockConsoleApplication();
        $cryptographer = new Oauth2Cryptographer([
            'keys' => [
                'test::' => $this->keys['test'],
            ],
            'defaultKeyName' => 'test::',
            'dataSeparator' => '::',
        ]);

        $this->expectExceptionMessage('Unable to encrypt, key name "test::" contains dataSeparator "::"');
        $cryptographer->encryp('test');
    }

    public function testDecryptWithInvalidSeparator()
    {
        $cryptographer = new Oauth2Cryptographer([
            'dataSeparator' => '::',
        ]);

        $this->expectExceptionMessage('Unable to decrypt, $data must be in format "keyName::ciphertext"');
        $cryptographer->decrypt('test:data');
    }

    public function testDecryptWithMissingKey()
    {
        $cryptographer = new Oauth2Cryptographer();

        $this->expectExceptionMessage('Unable to decrypt, no key with name "test" has been set');
        $cryptographer->decrypt('test::data');
    }

    public function testRotateKey()
    {
        $this->mockConsoleApplication();
        $cryptographer = new Oauth2Cryptographer([
            'keys' => $this->keys,
            'defaultKeyName' => 'test',
        ]);
        $data = 'secret';

        $ciphertext = $cryptographer->encryp($data);
        $this->assertStringStartsWith('test::', $ciphertext);

        $ciphertext = $cryptographer->rotateKey($ciphertext, 'new');
        $this->assertStringStartsWith('new::', $ciphertext);

        // Same key shouldn't change the data.
        $this->assertEquals($ciphertext, $cryptographer->rotateKey($ciphertext, 'new'));

        $plaintext = $cryptographer->decrypt($ciphertext);
        $this->assertEquals($data, $plaintext);

        // Rotate back to default key (no `newKeyName` specified).
        $ciphertext = $cryptographer->rotateKey($ciphertext);
        $this->assertStringStartsWith('test::', $ciphertext);
        $plaintext = $cryptographer->decrypt($ciphertext);
        $this->assertEquals($data, $plaintext);
    }
}
