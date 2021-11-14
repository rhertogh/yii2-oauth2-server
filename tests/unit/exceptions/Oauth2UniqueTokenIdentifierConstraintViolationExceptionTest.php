<?php

namespace Yii2Oauth2ServerTests\unit\exceptions;

use rhertogh\Yii2Oauth2Server\exceptions\Oauth2UniqueTokenIdentifierConstraintViolationException;
use Yii2Oauth2ServerTests\unit\TestCase;

/**
 * @covers \rhertogh\Yii2Oauth2Server\exceptions\Oauth2UniqueTokenIdentifierConstraintViolationException
 */
class Oauth2UniqueTokenIdentifierConstraintViolationExceptionTest extends TestCase
{
    public function testCreateWithDefaultMessage()
    {
        $this->expectExceptionMessage('Could not create unique access token identifier');
        throw Oauth2UniqueTokenIdentifierConstraintViolationException::create();
    }

    public function testCreateWithCustomMessage()
    {
        $message = 'my test message';
        $this->expectExceptionMessage($message);
        throw Oauth2UniqueTokenIdentifierConstraintViolationException::create($message);
    }
}
