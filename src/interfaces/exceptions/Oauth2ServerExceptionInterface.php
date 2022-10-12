<?php

namespace rhertogh\Yii2Oauth2Server\interfaces\exceptions;

interface Oauth2ServerExceptionInterface
{
    /**
     * Construct a new exception.
     *
     * @param string      $message        Error message
     * @param int         $code           Error code
     * @param string      $errorType      Error type
     * @param int         $httpStatusCode HTTP status code to send (default = 400)
     * @param null|string $hint           A helper hint
     * @param null|string $redirectUri    A HTTP URI to redirect the user back to
     * @param \Throwable   $previous       Previous exception
     */
    public function __construct(
        $message,
        $code,
        $errorType,
        $httpStatusCode = 400,
        $hint = null,
        $redirectUri = null,
        \Throwable $previous = null
    );
}
