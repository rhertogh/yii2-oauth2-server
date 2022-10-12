<?php

namespace rhertogh\Yii2Oauth2Server\exceptions;

use League\OAuth2\Server\Exception\UniqueTokenIdentifierConstraintViolationException;
use rhertogh\Yii2Oauth2Server\interfaces\exceptions\Oauth2ServerExceptionInterface;

// phpcs:ignore Generic.Files.LineLength.TooLong
class Oauth2UniqueTokenIdentifierConstraintViolationException extends UniqueTokenIdentifierConstraintViolationException implements
    Oauth2ServerExceptionInterface
{
    /**
     * @inheritDoc
     * @param null $message
     * @return static
     */
    public static function create($errorMessage = null)
    {
        if (empty($errorMessage)) {
            $errorMessage = 'Could not create unique access token identifier';
        }

        return new static($errorMessage, 100, 'access_token_duplicate', 500);
    }
}
