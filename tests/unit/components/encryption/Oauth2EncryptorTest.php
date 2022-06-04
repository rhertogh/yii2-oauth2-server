<?php

namespace Yii2Oauth2ServerTests\unit\components\encryption;

use Defuse\Crypto\Exception\EnvironmentIsBrokenException;
use rhertogh\Yii2Oauth2Server\components\encryption\Oauth2Encryptor;
use rhertogh\Yii2Oauth2Server\interfaces\components\factories\encryption\Oauth2EncryptionKeyFactoryInterface;
use Yii2Oauth2ServerTests\unit\TestCase;

/**
 * @covers \rhertogh\Yii2Oauth2Server\components\encryption\Oauth2Encryptor
 */
class Oauth2EncryptorTest extends TestCase
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
        $encryptor = new Oauth2Encryptor([
            'keys' => $this->keys,
            'defaultKeyName' => 'test',
        ]);
        $data = 'secret';

        $ciphertext = $encryptor->encryp($data);
        $this->assertFalse(strpos($ciphertext, $data));

        $plaintext = $encryptor->decrypt($ciphertext);
        $this->assertEquals($data, $plaintext);
    }

    public function testSetKeyInvalidKey()
    {
        $this->mockConsoleApplication();
        $this->expectExceptionMessage(
            'Encryption key "test" is malformed: Encoding::hexToBin() input is not a hex string.'
        );
        new Oauth2Encryptor([
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
                    // phpcs:ignore Generic.Files.LineLength.TooLong -- readability acually better on single line
                    Oauth2EncryptionKeyFactoryInterface::class => new class implements Oauth2EncryptionKeyFactoryInterface {
                        public function createFromAsciiSafeString($keyString, $doNotTrim = null)
                        {
                            throw new EnvironmentIsBrokenException('test message');
                        }
                    },
                ],
            ],
        ]);

        $this->expectExceptionMessage('Could not instantiate key "test": test message');
        new Oauth2Encryptor([
            'keys' => $this->keys,
        ]);
    }

    public function testGetSetDefaultKeyName()
    {
        $encryptor = new Oauth2Encryptor();

        $encryptor->setDefaultKeyName('test_default_key');
        $this->assertEquals('test_default_key', $encryptor->getDefaultKeyName());
    }

    public function testGetDefaultKeyNameWithoutItBeingSet()
    {
        $encryptor = new Oauth2Encryptor();

        $this->expectExceptionMessage('Unable to get the defaultKeyName since it is not set.');
        $encryptor->encryp('test');
    }

    public function testEncryptWithoutKey()
    {
        $encryptor = new Oauth2Encryptor();

        $this->expectExceptionMessage('Unable to encrypt, no key with name "non-existing" has been set');
        $encryptor->encryp('test', 'non-existing');
    }

    public function testEncryptWithoutDataSeparator()
    {
        $this->mockConsoleApplication();
        $encryptor = new Oauth2Encryptor([
            'keys' => $this->keys,
            'defaultKeyName' => 'test',
            'dataSeparator' => '',
        ]);

        $this->expectExceptionMessage('Unable to encrypt, dataSeparator is empty');
        $encryptor->encryp('test');
    }

    public function testEncryptWithoutKeyContainingDataSeparator()
    {
        $this->mockConsoleApplication();
        $encryptor = new Oauth2Encryptor([
            'keys' => [
                'test::' => $this->keys['test'],
            ],
            'defaultKeyName' => 'test::',
            'dataSeparator' => '::',
        ]);

        $this->expectExceptionMessage('Unable to encrypt, key name "test::" contains dataSeparator "::"');
        $encryptor->encryp('test');
    }

    public function testDecryptWithInvalidSeparator()
    {
        $encryptor = new Oauth2Encryptor([
            'dataSeparator' => '::',
        ]);

        $this->expectExceptionMessage('Unable to decrypt, $data must be in format "keyName::ciphertext"');
        $encryptor->decrypt('test:data');
    }

    public function testDecryptWithMissingKey()
    {
        $encryptor = new Oauth2Encryptor();

        $this->expectExceptionMessage('Unable to decrypt, no key with name "test" has been set');
        $encryptor->decrypt('test::data');
    }

    public function testRotateKey()
    {
        $this->mockConsoleApplication();
        $encryptor = new Oauth2Encryptor([
            'keys' => $this->keys,
            'defaultKeyName' => 'test',
        ]);
        $data = 'secret';

        $ciphertext = $encryptor->encryp($data);
        $this->assertStringStartsWith('test::', $ciphertext);

        $ciphertext = $encryptor->rotateKey($ciphertext, 'new');
        $this->assertStringStartsWith('new::', $ciphertext);

        // Same key shouldn't change the data.
        $this->assertEquals($ciphertext, $encryptor->rotateKey($ciphertext, 'new'));

        $plaintext = $encryptor->decrypt($ciphertext);
        $this->assertEquals($data, $plaintext);

        // Rotate back to default key (no `newKeyName` specified).
        $ciphertext = $encryptor->rotateKey($ciphertext);
        $this->assertStringStartsWith('test::', $ciphertext);
        $plaintext = $encryptor->decrypt($ciphertext);
        $this->assertEquals($data, $plaintext);
    }
}
