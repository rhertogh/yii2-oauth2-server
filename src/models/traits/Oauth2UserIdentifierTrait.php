<?php

namespace rhertogh\Yii2Oauth2Server\models\traits;

trait Oauth2UserIdentifierTrait
{
    /**
     * @inheritDoc
     */
    public function getUserIdentifier()
    {
        return $this->user_id;
    }

    /**
     * @inheritDoc
     */
    public function setUserIdentifier($identifier)
    {
        $this->user_id = $identifier;
    }
}
