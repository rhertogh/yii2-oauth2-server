<?php

namespace Yii2Oauth2ServerTests\unit\components\factories\encryption;

use Defuse\Crypto\Key;
use rhertogh\Yii2Oauth2Server\components\factories\encryption\Oauth2EncryptionKeyFactory;
use Yii2Oauth2ServerTests\unit\TestCase;

/**
 * @covers \rhertogh\Yii2Oauth2Server\components\factories\encryption\Oauth2EncryptionKeyFactory
 */
class Oauth2EncryptionKeyFactoryTest extends TestCase
{
    public function testCreateFromAsciiSafeString()
    {
        $key = (new Oauth2EncryptionKeyFactory())->createFromAsciiSafeString(
            // phpcs:ignore Generic.Files.LineLength.TooLong -- readability acually better on single line
            'def0000068fcf7a02625e841c263b227bb0ee04a42cb39b668a81e9b151e58f58d44fa15655e138a397b515482bea2688bd479647d41d084b82932215938d702f4e3b15c'
        );

        $this->assertInstanceOf(Key::class, $key);
    }
}
