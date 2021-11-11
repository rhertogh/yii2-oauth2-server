<?php

namespace rhertogh\Yii2Oauth2Server\exceptions;

class UniqueTokenIdentifierConstraintViolationException extends \League\OAuth2\Server\Exception\UniqueTokenIdentifierConstraintViolationException
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
