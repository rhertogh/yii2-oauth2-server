<?php

namespace Yii2Oauth2ServerTests\_helpers;

use Lcobucci\JWT\Configuration;
use League\OAuth2\Server\AuthorizationValidators\BearerTokenValidator;

class BearerTokenValidatorHelper extends BearerTokenValidator
{
    /**
     * @return Configuration
     */
    public function getJwtConfiguration()
    {
        return ClassHelper::getInaccessibleProperty($this, 'jwtConfiguration');
    }
}
