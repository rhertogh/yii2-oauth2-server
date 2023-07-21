<?php

namespace rhertogh\Yii2Oauth2Server\helpers\exceptions;

abstract class BaseEnvironmentVariableException extends \Exception
{
    /**
     * @var string Variable name
     */
    public $variable;

    public function __construct($variable, $message, $code = 0, \Throwable $previous = null) {
        $this->variable = $variable;
        parent::__construct($message, $code, $previous);
    }
}
