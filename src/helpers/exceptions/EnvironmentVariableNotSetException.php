<?php

namespace rhertogh\Yii2Oauth2Server\helpers\exceptions;

class EnvironmentVariableNotSetException extends BaseEnvironmentVariableException
{
    public function __construct($variable, $message = null, $code = 0, \Throwable $previous = null) {
        if ($message === null) {
            $message = 'The environment variable "' . $variable . '" is not defined.';
        }
        parent::__construct($variable, $message, $code, $previous);
    }
}
