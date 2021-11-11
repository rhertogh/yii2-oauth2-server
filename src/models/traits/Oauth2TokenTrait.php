<?php

namespace rhertogh\Yii2Oauth2Server\models\traits;

trait Oauth2TokenTrait
{
    /**
     * @inheritDoc
     */
    public function setRevokedStatus($isRevoked)
    {
        $this->enabled = !$isRevoked;
    }

    /**
     * @inheritDoc
     */
    public function getRevokedStatus()
    {
        return !$this->enabled;
    }
}
