<?php

namespace rhertogh\Yii2Oauth2Server\traits;

trait DefaultAccessTokenTtlTrait
{
    /**
     * @var \DateInterval|string|null
     * @see setDefaultAccessTokenTTL
     */
    protected $_accessTokenTTL = null;

    /**
     * @inheritdoc
     */
    public function getDefaultAccessTokenTTL()
    {
        if (is_string($this->_accessTokenTTL)) {
            $this->_accessTokenTTL = new \DateInterval($this->_accessTokenTTL);
        }
        return $this->_accessTokenTTL;
    }

    /**
     * @inheritdoc
     */
    public function setDefaultAccessTokenTTL($ttl)
    {
        if (is_string($ttl)) {
            $ttl = new \DateInterval($ttl);
        }
        $this->_accessTokenTTL = $ttl;
        return $this;
    }
}
