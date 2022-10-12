<?php

namespace rhertogh\Yii2Oauth2Server\interfaces\models\external\user;

interface Oauth2UserAuthenticatedAtInterface
{
    /**
     * Get the time the user was last authenticated or, if the user never logged in, their account creation time.
     * @return \DateTimeImmutable|null
     * @since 1.0.0
     */
    public function getLatestAuthenticatedAt();
}
