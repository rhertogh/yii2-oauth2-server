<?php

namespace rhertogh\Yii2Oauth2Server\components\server\tokens;

use yii\base\ArrayAccessTrait;
use yii\base\InvalidArgumentException;

class Oauth2AccessTokenData implements \IteratorAggregate, \ArrayAccess, \Countable, \JsonSerializable
{
    use ArrayAccessTrait;

    /**
     * @var array
     */
    protected $data;

    /**
     * @var int
     */
    protected $createdAt;

    public function __construct(array $data)
    {
        if (empty($data['access_token'])) {
            throw new InvalidArgumentException('$data must include key "access_token".');
        }

        $this->data = $data;
        $this->createdAt = time();
    }

    public function __serialize()
    {
        return $this->data;
    }

    public function __unserialize($data)
    {
        $this->data = $data;
    }


    public function getAccessToken()
    {
        return $this->data['access_token'];
    }

    public function getTokenType()
    {
        return $this->data['token_type'];
    }

    public function getRawExpiresIn()
    {
        return $this->data['expires_in'];
    }

    public function getRefreshToken()
    {
        return $this->data['refresh_token'];
    }

    public function getScope()
    {
        return $this->data['scope'];
    }

    public function jsonSerialize()
    {
        return $this->data;
    }
}
