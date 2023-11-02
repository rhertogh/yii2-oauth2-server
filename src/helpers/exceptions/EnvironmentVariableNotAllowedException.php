<?php

namespace rhertogh\Yii2Oauth2Server\helpers\exceptions;

class EnvironmentVariableNotAllowedException extends BaseEnvironmentVariableException
{
    public function __construct($variable, $message = null, $code = 0, \Throwable $previous = null)
    {
        if ($message === null) {
            $message = 'Usage of environment variable "' . $variable . '" is not allowed.';
        }
        parent::__construct($variable, $message, $code, $previous);
    }
}
