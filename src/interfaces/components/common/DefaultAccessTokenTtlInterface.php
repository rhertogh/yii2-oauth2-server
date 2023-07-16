<?php

namespace rhertogh\Yii2Oauth2Server\interfaces\components\common;

interface DefaultAccessTokenTtlInterface
{
    /**
     * @return \DateInterval|null The default Time To Live for the access token.
     *         When `null` the system default of 1 hour will be used.
     * @since 1.0.0
     */
    public function getDefaultAccessTokenTTL();

    /**
     * @ttl \DateInterval|string|null The default Time To Live for the access token.
     *         When using a string the format should be a DateInterval duration
     *         (https://www.php.net/manual/en/dateinterval.construct.php).
     *         When `null` the system default of 1 hour will be used.
     * @return $this
     * @since 1.0.0
     */
    public function setDefaultAccessTokenTTL($ttl);
}
