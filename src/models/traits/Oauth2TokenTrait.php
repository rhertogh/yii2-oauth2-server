<?php

namespace rhertogh\Yii2Oauth2Server\models\traits;

trait Oauth2TokenTrait
{
    /**
     * @inheritDoc
     */
    public function setRevokedStatus($isRevoked)
    {
        $this->enabled = (int)!$isRevoked;
    }

    /**
     * @inheritDoc
     */
    public function getRevokedStatus()
    {
        return !$this->enabled;
    }
}
